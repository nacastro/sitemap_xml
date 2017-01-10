<?php
set_time_limit(20);
// Requer o script da classe
require 'GerarSitemapClass.php';

// Cria uma nova instância da classe
$sitemap = new Sitemap();

$urlSite = $sitemap->UrlAtual();
//echo "A URL atual é: ". $gerarSitemap->UrlAtual();
//exit;
//
/* envio a URL recebida via parâmetro do CLI (Console)
if(isset($argv[1]) && !empty($argv[1]))
    $urlSite = $argv[1];
else*/
$urlSite = 'http://www.empregosn.com';

$sitemap->executar($urlSite);

// update robots.txt file
$sitemap->updateRobots();