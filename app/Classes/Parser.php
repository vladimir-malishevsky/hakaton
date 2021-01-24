<?php
namespace App\Classes;

use Illuminate\Support\Facades\Http;
use PhpQuery\PhpQuery;

class Parser
{
    public const URLS = [
        'https://novus.zakaz.ua/ru/categories/buckwheat/',
        'https://metro.zakaz.ua/ru/categories/buckwheat-metro/',
        'https://auchan.zakaz.ua/ru/categories/buckwheat-auchan/',
    ];

    public static function getGrechka(){
        $products = [];
        foreach (self::URLS as $url)
            $products = array_merge($products, self::parse($url));
        return $products;
    }

    public static function parse($url)
    {
        $pq = new PhpQuery();

        $html = Http::get($url);

        $pq->load_str($html->body());

        $elems = $pq->query('a.product-tile.jsx-725860710');

        $mass = [];

        $shop = explode('.', $url);

        foreach ($elems as $key => $el) {

            $new_html = $pq->outerHTML($el);
            $pq->load_str($new_html);
            $title                        = $el->getAttribute('title');
            $src                          = $el->getAttribute('href');
            $price                        = trim($pq->query('.Price__value_caption.jsx-3642073353')[0]->nodeValue);
            $weight                       = trim($pq->query('.product-tile__weight.jsx-725860710')[0]->nodeValue);
            $img                          = trim($pq->query('.product-tile__image-i.jsx-725860710')[0]->getAttribute('src'));
            $mass[$key]['title']          = utf8_decode($title);
            $mass[$key]['weight_per_one'] = utf8_decode(utf8_decode($weight));
            $mass[$key]['shop']           = explode('//',$shop[0])[1];
            $mass[$key]['img']            = $img;
            $mass[$key]['src']            = $shop[0] . '.zakaz.ua' . $src;
            $koef                         = 1;
            if (preg_match('/ г/', $mass[$key]['weight_per_one'])) {
                $entries = preg_split('/ /', $mass[$key]['weight_per_one']);
                $number  = (int) $entries[0];
                $koef    = 1000 / $number;
            }
            $mass[$key]['price_per_one'] = (int) $price;
            $mass[$key]['price_per_kg']  = (int) $price * $koef;

        }
        return $mass;
    }
}
