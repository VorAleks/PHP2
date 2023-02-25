<?php

namespace GeekBrains\LevelTwo\Http\Actions\Auth;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\AuthException;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class LogOut implements ActionInterface
{
    private const HEADER_PREFIX = 'Bearer ';

    public function __construct(
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository
    ) {
    }

    /**
     * @throws AuthException
     */
    public function handle(Request $request): Response
    {
        // Получаем HTTP-заголовок
        try {
            $header = $request->header('Authorization');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }
        // Проверяем, что заголовок имеет правильный формат
        if (!str_starts_with($header, self::HEADER_PREFIX)) {
            throw new AuthException("Malformed token: [$header]");
        }
        // Отрезаем префикс Bearer
        $token = mb_substr($header, strlen(self::HEADER_PREFIX));

        // получаем токен из репозитория
        $authToken = $this->authTokensRepository->get($token);

        $authToken->expiresOff();

        // Сохраняем токен в репозиторий
        $this->authTokensRepository->save($authToken);
        // Возвращаем токен
        return new SuccessfulResponse([
            'token expires off' => (string)$authToken,
        ]);
    }
}