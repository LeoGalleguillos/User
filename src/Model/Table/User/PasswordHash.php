<?php
namespace LeoGalleguillos\User\Model\Table\User;

use Laminas\Db\Adapter\Adapter;

class PasswordHash
{
    /**
     * @var Adapter
     */
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function updateWhereUserId(
        string $passwordHash,
        int $userId
    ): bool {
        $sql = '
            UPDATE `user`
               SET `user`.`password_hash` = ?
             WHERE `user`.`user_id` = ?
                 ;
        ';
        $parameters = [
            $passwordHash,
            $userId,
        ];
        return (bool) $this->adapter
                           ->query($sql)
                           ->execute($parameters)
                           ->getAffectedRows();
    }
}
