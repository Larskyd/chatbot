<?php
require_once __DIR__ . '/../models/QueryLogModel.php';

class HistoryController
{
    protected $logModel;

    public function __construct($db)
    {
        $this->logModel = new QueryLogModel($db);
    }

    public function handleRequest()
    {
        $history = $this->logModel->getRecent(5);
        include __DIR__ . '/../views/history.php';
    }
}