$(function(){

    function registerFias(cityInput, townInput, sourceUrl) {
        var city = $(cityInput);
        var street = $(townInput);

        city.change(function(){
            var value = $(this).val();

            $.ajax({
                url: sourceUrl,
                data: {cityId: value},
                dataType: 'html',
                success: function(html) {
                    street.replaceWith(html);
                }
            });
        });
    }

    registerFias('#city', '#street', 'loadStreets.php');
});