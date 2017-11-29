<?php
namespace IIA\Neoclick;

/**
 *
 * @author pawelszczepaniak
 *
 */
class NeoclickWidget
{
    private $neoclik;

    private $currency;

    private $type;

    private $correlationId;

    private $articles;

    private $dimensionsWidth;

    private $dimensionsHeight;

    private $dimensionsDepth;

    private $dimensionsWeight;

    private $signature;

    const neoclickWidgetResponse = array(
        'app_id_empty' => 'Puste app_id',
        'app_id_too_long' => 'app_id za długie',
        'currency_empty' => 'Brak podanej waluty',
        'currency_too_long' => 'Nazwa waluty zbyt długa',
        'type_empty' => 'Brak typu',
        'articles_empty' => 'Pusta lista artykułów',
        'article_id_empty' => 'Brakujący identyfikator artykułu',
        'article_name_empty' => 'Brakująca nazwa artykułu',
        'article_name_too_long' => 'Za długa nazwa artykułu',
        'article_price_too_low' => 'Cena artykułu za niska',
        'article_quantity_too_low' => 'Za mała liczba sztuk artykułu',
        'articles_ids_non_unique' => 'Powtórzone ID artykułu',
        'article_dimensions_empty' => 'Brak rozmiarów artykułów / paczki',
        'signature_invalid' => 'Sygnatura niepoprawna',
        'basket_internal_error' => 'Wewnętrzny błąd serwera podczas przetwarzania zapytania',
        'server_error' => 'Błąd po stronie serwera'
    );

    /**
     *
     * @return the $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     *
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @return the $correlationId
     */
    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    /**
     *
     * @return the $articles
     */
    public function getArticles()
    {
        return $this->articles;
    }

    public function getArticlesTable()
    {
        return json_decode($this->articles, true);
    }

    /**
     *
     * @return the $dimensionsWidth
     */
    public function getDimensionsWidth()
    {
        return $this->dimensionsWidth;
    }

    /**
     *
     * @return the $dimensionsHeight
     */
    public function getDimensionsHeight()
    {
        return $this->dimensionsHeight;
    }

    /**
     *
     * @return the $dimensionsDepth
     */
    public function getDimensionsDepth()
    {
        return $this->dimensionsDepth;
    }

    /**
     *
     * @return the $dimensionsWeight
     */
    public function getDimensionsWeight()
    {
        return $this->dimensionsWeight;
    }

    /**
     *
     * @return the $signature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     *
     * @param field_type $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     *
     * @param field_type $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     *
     * @param field_type $correlationId
     */
    public function setCorrelationId($correlationId)
    {
        $this->correlationId = $correlationId;
    }

    /**
     *
     * @param field_type $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     *
     * @param field_type $dimensionsWidth
     */
    public function setDimensionsWidth($dimensionsWidth)
    {
        $this->dimensionsWidth = $dimensionsWidth;
    }

    /**
     *
     * @param field_type $dimensionsHeight
     */
    public function setDimensionsHeight($dimensionsHeight)
    {
        $this->dimensionsHeight = $dimensionsHeight;
    }

    /**
     *
     * @param field_type $dimensionsDepth
     */
    public function setDimensionsDepth($dimensionsDepth)
    {
        $this->dimensionsDepth = $dimensionsDepth;
    }

    /**
     *
     * @param field_type $dimensionsWeight
     */
    public function setDimensionsWeight($dimensionsWeight)
    {
        $this->dimensionsWeight = $dimensionsWeight;
    }

    /**
     *
     * @param field_type $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     */
    public function __construct(Neoclick $neoclik)
    {
        $this->neoclik = $neoclik;
    }

    /**
     */
    function __destruct()
    {}

    /**
     */
    private function generateArticlesList()
    {

        // TODO
    }

    /**
     *
     * @return string
     */
    public function generateBasketSimple()
    {
        $out = '<body><head>
                <script>
                (function(d) {
                       var js, id = \'neoClick-jssdk\', ref = d.getElementsByTagName(\'script\')[0];
                       if (d.getElementById(id)) { return; }
                       js = d.createElement(\'script\'); js.id = id; js.async = true;
                       js.src = "https://widget.d.neoclick.io/sdk/neo-click.js";
                		ref.parentNode.insertBefore(js, ref);
                    }(document));

                	window.NeoClickAsyncInit = function() {

                       NeoClick.init({
                           appId: "' . $this->neoclik->getAppId() . '"
                       });

                       NeoClick.setBasket(
                    {
                       "currency": "' . $this->getCurrency() . '",
                       "type": "' . $this->getType() . '",
                       "correlationId": "' . $this->getCorrelationId() . '",
                       "articles": ' . $this->getArticles() . ',
                       "dimensions": {
                          "width": ' . $this->getDimensionsWidth() . ',
                          "height": ' . $this->getDimensionsHeight() . ',
                          "depth": ' . $this->getDimensionsDepth() . ',
                          "weight": ' . $this->getDimensionsWeight() . '
                       },
                       "signature": "' . $this->calculateSignature() . '"
                    }
                       );
                    };

                	</script>
                </head>
                <body>
                <p>Przykładowy koszyk NeoClick</p>

                <div class="neo-click-button" data-layout="standard" data-position="right"> </div>

                </body>';
        return $out;
    }

    /**
     *
     * @return string
     */
    public function generateBasketAdvanced()
    {
        $out = '<head>
        <script
          src="https://code.jquery.com/jquery-3.1.1.js"
          integrity="sha256-16cdPddA6VdVInumRGo6IbivbERE8p7CQR3HzTBuELA="
          crossorigin="anonymous"></script>
        <script src="https://widget.d.neoclick.io/sdk/neo-click.js"></script>
        </head>
        <body>
        <script>
             $(document).ready(function() {
             NeoClick.init({
                   appId: "6210638810678563195"
            },
               function(response) {

            });

               NeoClick.setBasket({

                "currency": "PLN",
                "type": "real",
                "correlationId": "AAAA-123456789",
                "articles": [
                  {
                     "id": "productA",
                     "name": "T-shirt",
                     "price": 2500,
                     "quantity": 1,
                     "dimensions": {
                        "width": 50,
                        "height": 30,
                        "depth": 5,
                        "weight": 300
                     }
                  },
                  {
                     "id": "productB",
                     "name": "Kubek biały",
                     "price": 300,
                     "quantity": 5,
                     "dimensions": {
                        "width": 20,
                        "height": 20,
                        "depth": 30,
                        "weight": 500
                     }
                  }
               ],
                "dimensions": {
                  "width": 300,
                  "height": 400,
                  "depth": 500,
                  "weight": 600
               },
                "signature": "b30dc34606332d0a85b473b21763d0bd6a02f92250de8cc147ebd6d89ac7d32f"
            });

            $(\'.updateAmmount\').click(function() {

        ajaxplugin.response(function(res) {

                    NeoClick.updateBasket(res);
                });
            });

           });
            </script>
            <input name="neo-click-button" class="neo-click-button" type="button">
            <input name="updateAmount" class="updateAmount" type="button">

            ';
        return $out;
    }

    /**
     *
     * @return string
     */
    public function calculateSignature()
    {
        $input = $this->neoclik->getAppId();
        foreach ($this->getArticlesTable() as $article) {

            $input .= $article['dimensions']['depth'] . $article['dimensions']['height'] . $article['dimensions']['weight'] . $article['dimensions']['width'] . $article['id'] . $article['name'] . $article['quantity'] . $article['price'];
        }

        $input .= $this->correlationId . $this->currency . $this->getDimensionsDepth() . $this->getDimensionsHeight() . $this->getDimensionsWeight() . $this->getDimensionsWidth() . $this->getType();

        return hash('sha256', $input . $this->neoclik->getSigningKey());
    }
}

