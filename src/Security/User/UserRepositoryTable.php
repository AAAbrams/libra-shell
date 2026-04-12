<?php

declare(strict_types=1);

namespace Libra\Shell\Security\User;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\UserTable;

final class UserRepositoryTable extends UserTable
{
    /**
     * @return array
     */
    public static function getMap(): array
    {
        return array_merge(parent::getMap(), [
            (new IntegerField("AUTH_VERSION"))
                ->configureDefaultValue(1),
            (new StringField("AUTH_REFRESH_HASH")),
        ]);
    }

    /**
     * @param int $userId
     * @return int
     * @throws UserRepositoryException
     */
    public static function getAuthVersion(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }
        try {
            return (int)self::query()
                ->setSelect(['AUTH_VERSION'])
                ->where('ID', $userId)
                ->fetch()['AUTH_VERSION'];
        } catch (\Exception $e) {
            throw new UserRepositoryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param int $userId
     * @return void
     * @throws UserRepositoryException
     */
    public static function increaseAuthVersion(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }
        $connection = self::getEntity()->getConnection();

        try {
            $connection->startTransaction();
            $sql = new SqlExpression(
                'SELECT ID, AUTH_VERSION FROM ?# WHERE ID = ?i FOR UPDATE SKIP LOCKED',
                self::getTableName(),
                $userId
            );
            $user = $connection->query($sql)->fetch();

            if ((int)$user['ID'] === 0) {
                throw new \Exception('Undefined user');
            }

            $updateSql = new SqlExpression(
                'UPDATE ?# SET AUTH_VERSION = AUTH_VERSION + 1 WHERE ID = ?i',
                self::getTableName(),
                $userId
            );
            $connection->query($updateSql);
            $connection->commitTransaction();
        } catch (\Exception $e) {
            $connection->rollbackTransaction();
            throw new UserRepositoryException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
