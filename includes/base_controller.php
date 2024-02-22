<?php

class BaseController
{
    public $db;
    public $pdo;
    public $functions;
    public $lang;
    public $requestHeader;
    private $isHasSession;
    public $selectedLang;

    public function __construct(bool $isHasSessionParam = true)
    {
        $this->isHasSession = $isHasSessionParam;
        $this->db = new Database();
        $this->pdo = $this->db->getPdo();
        $this->functions = new Functions();
        $this->requestHeader = $this->functions->headerRequest($this->isHasSession);
        $this->selectedLang = $this->requestHeader[0]['Accept-Language'];
        $this->lang = new Language($this->selectedLang);
    }
}
