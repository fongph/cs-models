<?php

namespace CS\Models\User\Aceptance;

use PDO,
    CS\Models\AbstractRecord;


/**
 * Class UserAceptanceRecord
 * @package CS\Models\User\Options
 */
class UserAceptanceRecord extends AbstractRecord
{


    protected $userId;
    protected $legalId;
    protected $legalVersionId;
    protected $createdAt;

    protected $keys = array(
        'id' => 'id',
        'userId' => 'user_id',
        'legalId' => 'legal_id',
        'legalVersionId' => 'legal_version_id',
        'createdAt' => 'created_at'
    );

    private function checkAceptance()
    {
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getLegalVersionId()
    {
        return $this->legalVersionId;
    }

    public function setLegalVersionId($legalVersionId)
    {
        $this->legalVersionId = $legalVersionId;
        return $this;
    }

    public function getLegalId()
    {
        return $this->legalId;
    }

    public function setLegalId($legalId)
    {
        $this->legalId = $legalId;
        return $this;
    }

    private function check()
    {
        $this->checkAceptance();
    }


    public function save()
    {
        $this->check();

        $userId = $this->escape($this->userId);
        $legalId = $this->escape($this->legalId);
        $legalVersionId = $this->escape($this->legalVersionId);

        if (!empty($this->id)) {
            return $this->updateRecord($userId, $legalId, $legalVersionId);
        } else {
            $this->id = $this->insertRecord($userId, $legalId, $legalVersionId);
        }

        return true;
    }


    /*

          $itemQuote = $this->pdo->quote($item);
                $legacies = $this->pdo->query("SELECT lv.`legal_version_id` as id, lv.`legal_id`
                                        FROM `legal_versions` lv
                                        LEFT JOIN `legal_types` lt ON  lv.`legal_id` = lt.`legal_id`
                                        WHERE lt.code = {$itemQuote} AND lv.`status` = 'active'
                                        ORDER BY lv.`created_at` DESC
                                        LIMIT 1;")->fetch();

                $userLegacyAcceptance = $this->pdo->query("SELECT id FROM users_acceptance WHERE user_id = {$orderRecord->getUserId()} AND legal_version_id = {$legacies['id']};")->fetch();

                if ($userLegacyAcceptance === false) {
                    $this->pdo->exec("INSERT INTO users_acceptance SET user_id = {$orderRecord->getUserId()}, legal_id = {$legacies['legal_id']}, legal_version_id = {$legacies['id']};");
                }

    */


    //todo: this should be in separate "legalVersions" model
    public function queryLegalVersions($code)
    {
        $codeQuote = $this->db->quote($code);
        if (($data = $this->db->query("SELECT lv.`legal_version_id` as id, lv.`legal_id`
                                        FROM `legal_versions` lv
                                        LEFT JOIN `legal_types` lt ON  lv.`legal_id` = lt.`legal_id`
                                        WHERE lt.code = {$codeQuote} AND lv.`status` = 'active'
                                        ORDER BY lv.`created_at` DESC
                                        LIMIT 1;")->fetch()) != false
        ) {
            return $data;
        }

        throw new UserAceptanceNotFoundException('Unable to load user option record');
    }

    public function queryAceptance($userId, $legalVersionId)
    {
        $escapedUserId = $this->db->quote($userId);
        $escapedLegalVersionId = $this->db->quote($legalVersionId);

        if (($data = $this->db->query("SELECT * FROM `users_acceptance` WHERE `user_id` = {$escapedUserId} AND `legal_version_id` = {$escapedLegalVersionId}")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new UserAceptanceNotFoundException('Unable to load Aceptance by userId and legalVersionId');

    }


    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at` FROM `users_acceptance` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new UserAceptanceNotFoundException('Unable to load users acceptance option record');
    }



    private function insertRecord($userId, $legalId, $legalVersionId)
    {

        $this->db->exec("INSERT INTO `users_acceptance` SET
                                     `user_id` = {$userId},
                                     `legal_id` = {$legalId},
                                     `legal_version_id` = {$legalVersionId};");

        return $this->db->lastInsertId();
    }


    private function updateRecord($userId, $legalId, $legalVersionId)
    {
        $rows = $this->db->exec("UPDATE `users_acceptance` SET
                                         `user_id` = {$userId},
                                         `legal_id` = {$legalId},
                                         `legal_version_id` = {$legalVersionId}
                                    WHERE `id` = {$this->id}
                                ");
        return ($rows > 0);
    }


}