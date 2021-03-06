<?php

namespace application\core;

abstract class Controller
{
    protected $route;
    protected $view;
    protected $acl;
    protected $model;

    public function __construct($route)
    {
        $this->route = $route;
        if (!$this->checkAcl()) {
            View::errorCode(403);
        }
        $this->view = new View($route);
        $this->model = $this->loadModel($route['controller']);
    }

    /**
     * Загрузка модели
     */
    public function loadModel($name)
    {
        $path = 'application\models\\' . ucfirst($name);
        if (class_exists($path)) {
            return new $path;
        }
    }

    /*
     * Проверка уровня доступа
     */
    public function checkAcl()
    {
        $this->acl = require ROOT . 'application/acl/' . $this->route['controller'] . '.php';
        if ($this->isAcl('all')) {
            return true;
        } elseif (isset($_SESSION['authorize']['id']) and $this->isAcl('authorize')) {
            return true;
        } elseif (isset($_COOKIE['auth']) and $this->isAcl('authorize')) {
            return true;
        } elseif (isset($_SESSION['admin']) and $this->isAcl('admin')) {
            return true;
        }
        return false;
    }

    public function isAcl($key)
    {
        return in_array($this->route['action'], $this->acl[$key]);
    }
}
