<?php
//namespace App\Classes;
//
//use Clue\React\Buzz\Browser;
//use PhpQuery\PhpQuery;
//use React\EventLoop\Factory;
//
//class SmartParser
//{
//
//    public function get_grechka()
//    {
//        $products = [];
//        $str      = '';
//        $start    = microtime(true);
////        echo "Fetching..." . "<br>";
//        $count = 0;
//
//        $loop   = Factory::create();
//        $client = new Browser($loop);
//        $mass   = array(
//            "https://auchan.zakaz.ua/ru/categories/buckwheat-auchan/",
//            'https://novus.zakaz.ua/ru/categories/buckwheat/',
//            'https://metro.zakaz.ua/ru/categories/buckwheat-metro/',
//        );
//
//        foreach ($mass as $url) {
//            $client
//                ->get($url)
//                ->then(function (\Psr\Http\Message\ResponseInterface $response) use (&$products, $url) {
//                    $domain   = "https://" . parse_url($url, PHP_URL_HOST);
//                    $products = array_merge(
//                        $products,
//                        $this->parser(
//                            $response->getBody(),
//                            $domain
//                        )
//                    );
//                });
//
//        }
//        $loop->run();
////        echo "...done in " . (microtime(true) - $start) .
////            '<br> ' .
////            'count :' . $count .
////            '<br>'
////            . ' arry size : ' . strlen($str) . PHP_EOL;
//
////        dd($products);
//        return $products;
//
//    }
//
//    public function parser($text, $domain)
//    {
////        echo "start parse... <br>" . PHP_EOL;
////        $start = microtime(true);
//
//        $pq = new PhpQuery();
//
//        $pq->load_str($text);
//
//
//        $elems = $pq->query('a.product-tile.jsx-725860710');
////        echo "mid parse... " . (microtime(true) - $start) . "<br>" . PHP_EOL;
//
//        $mass = [];
//
//        foreach ($elems as $key => $el) {
//            $new_html = $pq->outerHTML($el);
//            $pq->load_str($new_html);
//            $title                        = $el->getAttribute('title');
//            $src                          = $el->getAttribute('href');
//            $price                        = trim($pq->query('.Price__value_caption.jsx-3642073353')[0]->nodeValue);
//            $weight                       = trim($pq->query('.product-tile__weight.jsx-725860710')[0]->nodeValue);
//            $img                          = trim($pq->query('.product-tile__image-i.jsx-725860710')[0]->getAttribute('src'));
//            $mass[$key]['title']          = utf8_decode($title);
//            $mass[$key]['brand']          = utf8_decode($title);
//            $mass[$key]['weight_per_one'] = utf8_decode(utf8_decode($weight));
//            $mass[$key]['img']            = $img;
//            $mass[$key]['src']            = $domain . $src;
//            $koef                         = 1;
//            if (preg_match('/ г/', $mass[$key]['weight_per_one'])) {
//                $entries = preg_split('/ /', $mass[$key]['weight_per_one']);
//                $number  = (int) $entries[0];
//                $koef    = 1000 / $number;
//            }
//            $mass[$key]['price_per_one'] = (int) $price;
//            $mass[$key]['price_per_kg']  = (int) $price * $koef;
//        }
////        echo "end parse... " . (microtime(true) - $start) . "<br>" . PHP_EOL;
//
//        return $mass;
//
//    }
//}


namespace App\Classes;

use Clue\React\Buzz\Browser;
use DiDom\Document;
use React\EventLoop\Factory;

class SmartParser
{
    private $data;

    private $urls = [
        "https://auchan.zakaz.ua/ru/categories/buckwheat-auchan/",
        'https://novus.zakaz.ua/ru/categories/buckwheat/',
        'https://metro.zakaz.ua/ru/categories/buckwheat-metro/',
    ];


    public function get_all_grechka($params)
    {

        return $this->get_grechka($this->urls);
    }

    public function get_top_10_by_price()
    {
        $this->data = $this->get_grechka($this->urls);

        $mass = $this->data['data'];
        $size = count($mass);

        for ($i = 0; $i < $size - 1; $i++) {
            for ($j = 0; $j < $size - $i - 1; $j++) {
                if ($mass[$j]['price_per_kg'] > $mass[$j + 1]['price_per_kg']) {
                    $temp = $mass[$j];
                    $mass[$j] = $mass[$j + 1];
                    $mass[$j + 1] = $temp;
                }
            }
        }
        $top = array_slice($mass, 0, 10);
        return $top;
    }

    public function get_by_store(string $store)
    {
        $data = [];
        foreach ($this->urls as $url) {
            if (preg_match('/' . $store . '/', $url)) {
                $data = $this->get_grechka([$url]);
            }
        }
        return $data;
    }

    private function get_grechka($urls)
    {
        $products = ['data' => [], 'brands' => [], 'weights' => []];
        $loop = Factory::create();
        $client = new Browser($loop);
        foreach ($urls as $key => $url) {
            $client
                ->get($url)
                ->then(function (\Psr\Http\Message\ResponseInterface $response) use (&$products, $key, $url) {
                    $domain = "https://" . parse_url($url, PHP_URL_HOST);
                    $temp = $this->parser($response->getBody()->__toString(), $domain);
                    $products['data'] = array_merge(
                        $products['data'],
                        $temp['data']
                    );
                    $products['brands'] = $this->merge_brand_list($products['brands'], $temp['brands']);
                    $products['weights'] = $this->merge_weight_list($products['weights'], $temp['weights']);
                    unset($response);
                });

        }
        $loop->run();
        return $products;
    }

    public function parser($text, $domain)
    {
        $mass = [];
        $brands = [];
        $weights = [];

        $document = new Document($text);
        unset($text);

        $brandElems = $document
            ->first('.catalog-filters__widget.jsx-54661519')
            ->find('.SimpleCheckboxOptionAmounted.jsx-268113897');
        foreach ($brandElems as $key => $el) {
            $name = trim($el->first('.SimpleCheckboxOptionAmounted__text.jsx-268113897')->text());
            $count = (int)trim($el->first('.SimpleCheckboxOptionAmounted__amount.jsx-268113897')->text());
            $brands[$key]['brand'] = $name;
            $brands[$key]['count'] = $count;
        }

        $elems = $document->find('.product-tile');

        foreach ($elems as $key => $el) {

            $title = $el->attr('title');
            $src = $el->getAttribute('href');
            $price = trim($el->first('.Price__value_caption.jsx-3642073353')->text());

            $weight = trim($el->first('.product-tile__weight.jsx-725860710')->text());
            $img = trim($el->first('.product-tile__image-i.jsx-725860710')->attr('src'));

            $mass['data'][$key]['title'] = $title;
            $mass['data'][$key]['brand'] = 'Невідомо';
            foreach ($brands as $brand){
                if (stristr(strtolower($title), strtolower($brand['brand'])) !== false){
                    $mass['data'][$key]['brand'] = $brand['brand'];
                    break;
                }
            }

            $mass['data'][$key]['weight_per_one'] = $weight;
            $weights[] = $weight;
            $mass['data'][$key]['img'] = $img;
            $mass['data'][$key]['store'] = preg_split("/\./", parse_url($domain, PHP_URL_HOST))[0];

            $mass['data'][$key]['src'] = $domain . $src;

            $koef = 1;
            if (preg_match('/ г/', $mass['data'][$key]['weight_per_one'])) {

                $number = (int)preg_replace('/[^0-9]/', '', $mass['data'][$key]['weight_per_one']);
                $mass['data'][$key]['weight_type'] = 'g';
                $koef = 1000 / $number;

            } else {
                $number = (int)preg_replace('/[^0-9]/', '', $mass['data'][$key]['weight_per_one']);
                $koef = 1 / $number;
                $mass['data'][$key]['weight_type'] = 'kg';
                // ;

            }

            $mass['data'][$key]['price_per_one'] = (int)$price;
            $mass['data'][$key]['price_per_kg'] = (int)$price * $koef;

        }
        unset($elems);
        unset($document);


        $mass['brands'] = $brands;

        $mass['weights'] = $weights;

        return $mass;
    }

    public function merge_brand_list($mass1, $mass2)
    {
        $ans = 1;
        $index = 0;
        if (count($mass1) != 0) {
            foreach ($mass2 as $key2 => $el2) {
                $ans = true;
                foreach ($mass1 as $key1 => $el1) {
                    if ($el1['brand'] == $el2['brand']) {
                        $index = $key1;
                        $ans = $ans * 0;
                    }
                }
                if (!$ans) {
                    $mass1[$index] = [
                        'brand' => $el2['brand'],
                        'count' => $el2['count'] + $mass1[$index]['count'],
                    ];
                } else {
                    $mass1[] = $el2;
                }
            }
            return $mass1;

        } else {
            return $mass2;
        }
    }

    public function merge_weight_list($mass1, $mass2)
    {
        if (count($mass1) != 0) {
            $arr = array_unique(array_merge($mass1, $mass2));
            return $arr;
        } else {
            return $mass2;
        }
    }
}
