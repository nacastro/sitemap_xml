<?php
set_time_limit(20);
// Requer o script da classe
require 'GerarSitemapClass.php';

// Cria uma nova inst�ncia da classe
$sitemap = new Sitemap();

$urlSite = $sitemap->UrlAtual();
//echo "A URL atual �: ". $gerarSitemap->UrlAtual();
//exit;
//
/* envio a URL recebida via par�metro do CLI (Console)
if(isset($argv[1]) && !empty($argv[1]))
    $urlSite = $argv[1];
else*/
$urlSite = 'http://www.empregosn.com';

$sitemap->executar($urlSite);

// update robots.txt file
$sitemap->updateRobots();