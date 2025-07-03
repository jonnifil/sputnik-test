<?php

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request as RequestFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApiController extends Controller
{

    protected $repository;

    /**
     * @param $method
     * @param $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        if ($this->repository instanceof BaseRepository) {
            $request = RequestFacade::instance();
            if ($request->has('authFilter')) {
                $this->repository->setAuthFilter($request->get('authFilter'));
            }
        }

        return $this->{$method}(...array_values($parameters));
    }

    /**
     * @param $item
     *
     * @return JsonResponse
     */
    public function respondWithCollection($collection): JsonResponse
    {
        return ($collection)->additional(['code' => Response::HTTP_OK])
            ->response();
    }

    /**
     * @param $item
     *
     * @return JsonResponse
     */
    public function respondWithItem($item): JsonResponse
    {
        return ($item)->additional(['code' => Response::HTTP_OK])
            ->response();
    }

    /**
     * @param $item
     *
     * @return JsonResponse
     */
    public function respondCreateItem($item): JsonResponse
    {
        return ($item)->additional([
            'message' => 'Успешно создано',
            'code' => Response::HTTP_CREATED,
        ])->response();
    }

    /**
     * @param $item
     *
     * @return JsonResponse
     */
    public function respondUpdateItem($item): JsonResponse
    {
        return ($item)->additional([
            'message' => 'Успешно обновлено',
            'code' => Response::HTTP_ACCEPTED,
        ])->response();
    }

    /**
     * @param $item
     *
     * @return JsonResponse
     */
    public function respondRestoreItem($item): JsonResponse
    {
        return ($item)->additional([
            'message' => 'Успешно восстановлено',
            'code' => Response::HTTP_OK,
        ])->response();
    }

    /**
     * @param $error
     * @return mixed
     */
    public function respondUpdateFail($error = ''): JsonResponse
    {
        return $this->respondWithArray([
            'message' => 'Ошибка при обновлении',
            'errors' => [
                'error' => $error,
            ],
            'code' => Response::HTTP_BAD_REQUEST,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondDeleteItem(): JsonResponse
    {
        return $this->respondWithMessage('Успешно удалено');
    }

    public function respondDeleteFail(): JsonResponse
    {
        return $this->respondWithArray([
            'message' => 'Ошибка при удалении',
        ], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function respond($data, string $message = '', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $array = ['data' => $data];
        if (!empty($message)) {
            $array['message'] = $message;
        }
        $array['code'] = $statusCode;

        return response()->json($array, Response::HTTP_OK, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Respond the data.
     *
     * @param array $array
     * @param int   $statusCode
     * @param array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithArray(array $array, int $statusCode = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        $array['code'] = $statusCode;

        return response()->json($array, $statusCode, $headers);
    }

    /**
     * Respond the data.
     *
     * @param array $array
     * @param int   $statusCode
     * @param array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithDataArray(array $array, int $statusCode = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return $this->respondWithArray(['data' => $array], $statusCode, $headers);
    }

    /**
     * Respond the message.
     *
     * @param $message
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithMessage($message, $status = Response::HTTP_OK): JsonResponse
    {
        return $this->respondWithArray([
            'message' => $message,
            'code' => $status,
        ], $status);
    }

    /**
     * @param \SplFileInfo|string $file
     * @param string|null         $name
     * @param array               $headers
     *
     * @return BinaryFileResponse
     */
    public function respondDownloadFile($file, string $name = null, array $headers = []): BinaryFileResponse
    {
        return response()->download($file, $name, $headers);
    }

    public function respondServerError(): JsonResponse
    {
        return $this->respond(null, 'Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function respondUpdateArray(array $array): JsonResponse
    {
        return $this->respondWithArray(['data' => $array, 'message' => 'Успешно обновлено']);
    }

    public function respondNotFound(): JsonResponse
    {
        return self::notFound();
    }

    public static function notFound(): JsonResponse
    {
        return response()->json([
            'message' => 'Не существует',
            'code' => Response::HTTP_NOT_FOUND,
        ], Response::HTTP_NOT_FOUND);
    }


    public static function noContent(): JsonResponse
    {
        return response()->json([
            'message' => 'В обработке',
            'code' => Response::HTTP_NO_CONTENT,
        ], Response::HTTP_NO_CONTENT);
    }

    public static function badRequest($message = 'Ошибочный запрос')
    {
        return response()->json([
            'message' => $message,
            'code' => Response::HTTP_BAD_REQUEST,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return JsonResponse
     */
    public static function unauthorized(): JsonResponse
    {
        return response()->json([
            'message' => 'Не выполнен вход',
            'code' => Response::HTTP_UNAUTHORIZED,
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param $addMessage
     * @return JsonResponse
     */
    public static function forbidden($addMessage = ''): JsonResponse
    {
        return response()->json([
            'message' => 'Доступ запрещён' . $addMessage,
            'code' => Response::HTTP_FORBIDDEN,
        ], Response::HTTP_FORBIDDEN);
    }

    public static function privilegeForbidden(string $privilegeCode): JsonResponse
    {
        return response()->json([
            'message' => 'This action is unauthorized',
            'requiredPrivilege' => $privilegeCode,
            'code' => Response::HTTP_FORBIDDEN,
        ], Response::HTTP_FORBIDDEN);
    }
}
