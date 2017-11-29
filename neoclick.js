<script  type="text/javascript"  src="https://widget.d.neoclick.io/sdk/neo-click.js"></script>
<script type="text/javascript">
var input =
    6228863472927513479
    + 10 + 10
    + 100 + 10
    + '[iai:itemcardpage_product_id]' + '[iai:itemcardpage_product_name]' + 1 + '[iai:itemcardpage_product_price_gross_float]'
    + 'AA' + 'PLN'
    + 100 + 100 + 100 + 100
    + 'real';
var signingKey = '5b812b04690463ca23d2113758ff8efa';


$(document).ready(function() {

                                alert('OK');
                               $.post( "http://iai.iia.pl/index.php", { name: "John", time: "2pm" })
                                 .done(function( data ) {
                                  alert( "Data Loaded: " + data );
                                });


                     	        NeoClick.init({
                     	             appId: "6228863472927513479"
                     	          },
                     	          function(response) {

                     	          });

                                NeoClick.setBasket(
                                {
                                   "currency": "PLN",
                                   "type": "real",
                                   "correlationId": "",
                                   "articles": [{"id":"'[iai:itemcardpage_product_id]'","name":"'[iai:itemcardpage_product_name]'","price":'[iai:itemcardpage_product_price_gross_float]',"quantity":1,"dimensions":{"weight":100,"width":10,"height":10,"depth":10}}],
                                   "dimensions": {
                                      "width": 100,
                                      "height": 100,
                                      "depth": 100,
                                      "weight": 100
                                   },
                                   "signature": input
                                 });
                     	//
                     	});
</script>