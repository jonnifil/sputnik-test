<?php

namespace App\Repositories;

use App\Services\Auth\FilterMap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BaseRepository
{

    protected Model $model;

    protected array $filters = [];

    protected string $authFilter = '';

    protected array $sort = ['property' => 'id', 'direction' => 'ASC'];


    public function __construct(Request $request)
    {
        if ($request->has('filters')) {
            $this->filters = json_decode($request->get('filters'), true);
        }

        if ($request->has('sort')) {
            $this->sort = json_decode($request->get('sort'), true);
        }
    }

    /**
     * @return Builder
     */
    protected function newQuery(): Builder
    {
        $tableName = $this->model->getTable();
        return $this->model::query()->select([$tableName . '.*']);
    }

    public function setAuthFilter(string $filter)
    {
        $this->authFilter = $filter;
    }

    /**
     * @param int $id
     * @return Builder|Model
     */
    public function getById(int $id)
    {
        return $this->newQuery()
            ->where('id', '=', $id)
            ->firstOrFail();
    }

    public function getListQuery(): Builder
    {
        return $this->applyFilters($this->applyAuthFilter($this->newQuery()));
    }

    public function getList(): Collection
    {
        return $this->getListQuery()
            ->get();
    }

    protected function getPerPage(Request $request): int
    {
        $perPage = 20;
        if ($request->has('length')) {
            $perPage = $request->get('length');
        }

        return $perPage;
    }

    protected function getPage(Request $request, int $perPage): int
    {
        $page = 1;
        if ($request->has('page')) {
            $page = $request->get('page');
        }

        if ($request->has('start')) {
            $offset = $request->get('start');
            $page = 1 + floor($offset / $perPage);
        }

        return $page;
    }

    /**
     * @param Request $request
     * @param Builder|null $query
     * @return LengthAwarePaginator
     * @throws ValidationException
     */
    public function getPaginateList(Request $request, ?Builder $query = null): LengthAwarePaginator
    {
        $perPage = $this->getPerPage($request);

        $page = $this->getPage($request, $perPage);

        $query = $query ?? $this->newQuery();

        return $this->applyFilters($this->applyAuthFilter($query))
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function applyFilters(Builder $query)
    {
        $tableFields = Schema::getColumnListing($this->model->getTable());
        foreach ($this->filters as $key => $filter) {
            if (!isset($filter['value'])) {
                continue;
            }
            $field = $filter['property'];
            if (!in_array($field, $tableFields)) {
                throw ValidationException::withMessages([$field => "Фильтрация по полю {$field} невозможна"]);
            }
            $value = $filter['value'];
            $comparison = !array_key_exists('comparison', $filter) || empty($filter['comparison'])
                ? 'eq' : $filter['comparison'];
            $type = $filter['type'] ?? 'string';

            switch ($type) {
                case 'string':
                    switch ($comparison) {
                        case 'eq':
                            $query->where($field, '=', $value);
                            break;
                        case 'in':
                            if (is_array($value) && $value) {
                                $query->whereIn($field, $value);
                            }
                            break;
                        case 'like':
                            $query->where($field, 'ilike', '%'.$value.'%');
                            break;
                        default:
                            $query->where($field, '=', $value);
                    }
                    break;
                case 'list':
                    if (is_array($value) && $value) {
                        $query->whereIn($field, $value);
                    } else {
                        $query->where($field, '=', $value);
                    }
                    break;
                case 'boolean':
                    if (!is_bool($value)) {
                        throw ValidationException::withMessages([$field => "Значение фильтра для типа boolean должно быть 'true' или 'false'"]);
                    }
                    $query->where($field, '=', $value == 'true' ? 1 : 0);
                    break;
                case 'numeric':
                    switch ($comparison) {
                        case 'eq':
                            $query->where($field, '=', (double)$value);
                            break;
                        case 'neq' :
                            $query->where($field, '!=', (double)$value);
                            break;
                        case 'lt' :
                            $query->where($field, '<', (double)$value);
                            break;
                        case 'abovezero':
                            $query->where($field, '>', 0);
                            break;
                        case 'gt' :
                            $query->where($field, '>', (double)$value);
                            break;
                        case 'le' :
                            $query->where($field, '<=', (double)$value);
                            break;
                        case 'ge' :
                            $query->where($field, '>=', (double)$value);
                            break;
                    }
                    break;
                case 'date':
                    if (!is_array($value)) {
                        $this->checkDateTime($field, $value);
                    }
                    switch ($comparison) {
                        case 'eq':
                            $query->whereDate($field, '=', $value);
                            break;
                        case 'lt':
                            $query->whereDate($field, '<', $value);
                            break;
                        case 'gt':
                            $query->whereDate($field, '>', $value);
                            break;
                        case 'le':
                            $query->whereDate($field, '<=', $value);
                            break;
                        case 'ge':
                            $query->whereDate($field, '>=', $value);
                            break;
                        case 'between':
                            if (is_array($value) && count($value) == 2) {
                                $this->checkDateTime($field, $value[0]);
                                $this->checkDateTime($field, $value[1]);
                                $query->where(function (Builder $q) use ($field, $value) {
                                    $q->whereDate($field, '>=', $value[0]);
                                    $q->whereDate($field, '<=', $value[1]);
                                });
                            }
                            break;
                    }

            }

            unset($this->filters[$key]);
        }

        return $this->applySort($query);
    }

    protected function applySort(Builder $query): Builder
    {
        $table = $this->model->getTable();
        return $query->orderBy($table . '.' . $this->sort['property'], $this->sort['direction']);
    }

    public function applyAuthFilter(Builder $query): Builder
    {
        if ($filter = $this->authFilter) {
            $filterClass = FilterMap::$filters[$filter];
            $query = (new $filterClass)->filter($query);
        }
        return $query;
    }

    public function can(Model $model): bool
    {
        return $this->applyAuthFilter($this->newQuery())
            ->where('id', '=', $model->id)
            ->exists();
    }

    protected function checkDateTime($field, $value)
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if (!$date) {
            throw ValidationException::withMessages([$field => "Неверное значение для даты"]);
        }
    }

    public function valToArray(mixed $value): array
    {
        return is_array($value) ? $value : [$value];
    }

}
