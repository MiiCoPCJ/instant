<?php

namespace frontend\data;
use frontend\models\UserModel;
use hzfw\base\BaseObject;
use hzfw\web\Config;

class UserContext extends BaseObject
{
    private $db;
    private $config;
    
    public function __construct(Config $config, \DefaultDb $db)
    {
        $this->db = $db;
        $this->config = $config;
    }
    
    public function GetUser(int $id): UserModel
    {
        $sql = "SELECT `id`, `name`, `password` FROM `user` WHERE `id` = :id";
        $model = UserModel::Parse($this->db->QueryOne($sql, [":id" => $id]));
        return $model;
    }
}