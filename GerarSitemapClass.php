<?php

/**
 *
 * @author
 * @version    1.1
 */
class Sitemap
{
    /**
     * Função principal para a gereção de sitemaps via console app
     * @param string $urlCLI
     * @return void
     */
    public function executar($urlCLI)
    {
        // ECHO imprime na tela informações de feedback para o usuário
        echo "Iniciando...</br>";

        // atribuí o valor recebido por parãmetro para as variáveis
        $siteUrl = $buscaUrl = $urlCLI;

        echo "Buscando URLs da pagina inicial... </br>";

        // chama a função para pegar as URLs
        $urlsEncontradas = $this->getUrls($siteUrl, $buscaUrl);
        $totalEncontrado = count($urlsEncontradas);

        echo "Concluido.</br>";
        echo "Verificando as outras paginas...</br>";

        $i = 1;
        // Percorre o array com as URLs encontradas
        foreach ($urlsEncontradas as $buscaUrlRes){
            // Evita percorrer URLs de fora do domínio atual
            if(strpos($buscaUrlRes, $siteUrl) !== false){

                // Busca as URLs filhas
                $urlsEncontradas = array_merge($urlsEncontradas, $this->getUrls($siteUrl, $buscaUrlRes));

                // Limpa as URLs repetidas para reduzir o consumo de memória, porém faz apenas a cada 10, para economizar processamento
                if($i % 10 == 0){
                    echo "Limpando URLs duplicadas da memoria... </br>";
                    $urlsEncontradas = array_unique($urlsEncontradas);
                    echo "Concluido.</br>";
                }

                echo "Analisando pagina {$i} de " . $totalEncontrado . " (" . count($urlsEncontradas) . " URLs encontradas)..</br>";
                $i++;
            }
        }

        echo "Limpando URLs duplicadas da memoria... </br>";
        $urlsEncontradas = array_unique($urlsEncontradas);
        echo "Concluido.</br>";

        echo "Gerando arquivo...</br> ";

        // Inicia a variável que vai receber o conteúdo da saída XML
        $conteudoXML =
'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
    ';

        $conteudoXML .= "
        <url>
        <loc>{$urlCLI}</loc>
        <changefreq>daily</changefreq>
        <priority>1.00</priority>
        <lastmod>2011-04-07</lastmod>
        </url>";

        $tmp_termos_filtro = $this->getFiltro();

        // Percorre o array das URLs final e guarda na variável
        foreach ($urlsEncontradas as $url){

            if ($url == $urlCLI || $urlCLI == $url . '/') {
                continue;
            }

            // filtros - INICIO
            $valido = true;
            foreach ($tmp_termos_filtro as $filtrado) {
                if (strpos($url, $filtrado) !== false) $valido = false;
            }
            if (strpos($url, 'www.empregosn') === false) $valido = false;
            // filtros - FIM

            if ($valido) {
                $conteudoXML .= "
                <url>
                    <loc>{$url}</loc>
                    <changefreq>daily</changefreq>
                    <priority>0.80</priority>
                    <lastmod>2011-04-07</lastmod>
                </url>";
            }
        }

        $conteudoXML .=
        "
            </urlset>";

        // Abre o arquivo que será escrito
        $arquivoXML = fopen(DIRNAME(__FILE__).'/sitemap.xml', 'w') or die('Erro ao abrir o arquivo XML');
        // Escreve o arquivo
        fwrite($arquivoXML, $conteudoXML);
        // Fecha o arquivo após a escrita
        fclose($arquivoXML);

        echo "Concluido.</br>";
    }

    /**
     * Lê uma página e pega as URLs do atributo href
     * @param string $url
     * @return array
     */
    public function getUrls($siteUrl, $buscaUrl)
    {
        // lê a página
        $conteudoUrl = file_get_contents($buscaUrl);

        // pega o conteudo do href
        preg_match_all("/href\=\"([a-zA-Z_\.0-9\/\-\! :\@\$]*)\"/i", $conteudoUrl, $encontrados);
        // formata as URLs
        $urlsEncontradas = $this->formataUrls($encontrados[1], $siteUrl);

        return array_unique($urlsEncontradas);
    }

    /**
     * Formata e padroniza as URLs
     * @param array $urlsEncontradas
     * @param string $siteUrl
     * @return array
     */
    public function formataUrls($urlsEncontradas, $siteUrl)
    {
        // Percorre o array das URLs atual
        for ($i = 0; $i < count($urlsEncontradas); $i++){
            // Se não encontrar o prefixo do site atual, incluí
            if(strpos($urlsEncontradas[$i], 'http://') === false && strpos($urlsEncontradas[$i], 'https://') === false){
                $urlsEncontradas[$i] = $siteUrl . $urlsEncontradas[$i];
            }
        }

        return $urlsEncontradas;
    }

    public function getFiltro()
    {
        return array(
            '.css',
            'youtube.com',
            'facebook.com',
            'google.com',
            'twitter.com',
            'correios.com',
            'correios.net',
            'ebit.com',
            'www.sslshopper.com',
            'pubsites.com.br',
            'feedburner.com',
            'pinterest.com',
            'jssor.com'
        );
    }

    function UrlAtual(){
         $dominio= $_SERVER['HTTP_HOST'];
         $url = "http://" . $dominio;
         return $url;
    }

     /**
     * If robots.txt file exist, will update information about newly created sitemaps.
     * If there is no robots.txt will, create one and put into it information about sitemaps.
     * @access public
     */
    public function updateRobots() {
        if (!isset($this->sitemaps))
            throw new BadMethodCallException("To update robots.txt, call createSitemap function first.");
        $sampleRobotsFile = "User-agent: *\nAllow: /";
        if (file_exists($this->basePath.$this->robotsFileName)) {
            $robotsFile = explode("\n", file_get_contents($this->basePath.$this->robotsFileName));
            $robotsFileContent = "";
            foreach($robotsFile as $key=>$value) {
                if(substr($value, 0, 8) == 'Sitemap:') unset($robotsFile[$key]);
                else $robotsFileContent .= $value."\n";
            }
            $robotsFileContent .= "Sitemap: $this->sitemapFullURL";
            if ($this->createGZipFile && !isset($this->sitemapIndex))
                $robotsFileContent .= "\nSitemap: ".$this->sitemapFullURL.".gz";
            file_put_contents($this->basePath.$this->robotsFileName,$robotsFileContent);
        }
        else {
            $sampleRobotsFile = $sampleRobotsFile."\n\nSitemap: ".$this->sitemapFullURL;
            if ($this->createGZipFile && !isset($this->sitemapIndex))
                $sampleRobotsFile .= "\nSitemap: ".$this->sitemapFullURL.".gz";
            file_put_contents($this->basePath.$this->robotsFileName, $sampleRobotsFile);
        }
    }
}