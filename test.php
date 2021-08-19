<?php

$on = $false;

$url =
    "https://bmb56.ru/companies/90-merkurij-salon-mebeli/tovary-dla-doma/odeala-2";
$parent = 131;
$max_count_product = "";
$template = 5;

if ($on) {
    require_once MODX_BASE_PATH . "assets/template/php/simple_html_dom.php";
    $html = file_get_html($url);

    $post = [];
    foreach ($html->find(".section__item") as $element) {
        $title = $element->find(".goods-preview__name", 0);
        $price = $element->find(".goods-preview__price", 0);
        $img = $element->find(".goods-preview__photo-wrap img", 0);

        $post[] = [
            "title" => $title->plaintext,
            "price" => $price->plaintext,
            "img" => $img->src,
        ];
    }

    if ($max_count_product == "") {
        $max_count_product = $post;
    }

    for ($i = 0; $i < count($max_count_product); $i++) {
        $input = "https://bmb56.ru" . $post[$i][img];
        $output =
            MODX_BASE_PATH .
            "assets/template/testimg/" .
            time() .
            "" .
            $i .
            ".png";
        file_put_contents($output, file_get_contents($input));

        $product_price = $post[$i][price];
        $product_price = preg_replace("/\s+/ui", "", $product_price);
        $product_price = intval($product_price);

        $product_title = $post[$i][title];
        $product_img = $output;

        $response = $modx->runProcessor("resource/create", [
            "class_key" => "msProduct",
            "pagetitle" => $product_title,
            "parent" => $parent,
            "template" => $template,
            "show_in_tree" => 0,

            //Данные
            "price" => $product_price,
            "old_price" => 0,
            "favorite" => 0,
            "popular" => 0,

            //стандартные опции товара
            "color" => 0,
            "size" => 0,
            "tags" => 0,
        ]);

        // добавление изображения после создания ресурса

        $id = $response->response["object"]["id"]; //id товара

        $gallery = [
            "id" => $id,
            "name" => "",
            "rank" => 0,
            "file" => $output,
        ];

        $upload = $modx->runProcessor("gallery/upload", $gallery, [
            "processors_path" =>
                MODX_CORE_PATH . "components/minishop2/processors/mgr/",
        ]);

        if ($upload->isError()) {
            print_r($upload->getResponse());
        }
    }

    echo "Парсинг товаров в $parent категорию завершен";
} else {
    echo "Парсер выключен";
}
