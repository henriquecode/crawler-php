<?php

/**
 * Crawler para estudo
 * 
 * Usando a página de eventos do SYMPLA para exemplo
 * 
 * Usando DOMDocument, DomXpath e serviço Proxycrawl (https://proxycrawl.com).
 * 
 * DOMDocument - Para gerar a estrutura de documento em objeto
 * DomXpath - Classe usada para navgear nessa estrutura
 * Proxycrawl - Para servidor proxy, claro com muitos serviços disponíveis
 * 
 */


/** 
 * Alguns erros podem acontecer na hora de tentar obter o objeto DOMDocument, 
 * por isso desabilito. Esses erros é 100% de certeza que vão acontecer por que não
 * podemos garantir a boa formatação do html vindo de outros sites
 */
libxml_use_internal_errors(true);

const URL = 'https://www.sympla.com.br/eventos/vem-ai';
const TOKEN = '<TOKEN_JS_PROXY_CRAWL>';

$url = urlencode(URL);
$events = [];

# CURL para requisição do serviço
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.proxycrawl.com/?token=".TOKEN."&format=json&page_wait=3000&url=$url");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$response = json_decode($response, true);
$html = $response['body'];

# DOMDocumento usando loadHTML para retornar objeto
$doc = new DOMDocument();
$doc->loadHTML($html);

# DomXpath para retornar objeto de navegação
$xpath = new DomXpath($doc);
$elements = $xpath->query("//a[@class='sympla-card w-inline-block']"); # Buscando div com classe igual: sympla-card w-inline-block

echo '<pre>';
foreach ($elements as $element) {

        $image = $xpath->query("*/div[@class='event--image']/@style", $element); # Buscando atributo 'style' na div
        $datetime = $xpath->query("*/div[@class='event-card-data-block']", $element);
        $title = $xpath->query("*/div[@class='event-name event-card']", $element);
        $location = $xpath->query("*/div[@class='event-location event-card']", $element);

        $events[] = [
                'image' => str_replace(['background-image:url(\'', '\');'], [''], preg_replace('/\s+/', '', $image->item(0)->textContent)),
                'title' => trim($title->item(0)->textContent),
                'datetime' => $datetime->item(0)->textContent ?? 'Sem informação',
                'location' => trim($location->item(0)->textContent)
        ];
}

print_r($events);