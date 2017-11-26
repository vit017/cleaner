    </div> <!-- page-container closing tag-->
    <!-- scripts -->
    <script src="//yandex.st/jquery/1.11.0/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="/layout/assets/js/vendor/jquery-1.11.0.min.js"><\/script>')</script>
    <script src="/layout/assets/js/vendor/plugins.min.js"></script>
    <script src="/layout/assets/js/main.js"></script>
    <script>window.viewportUnitsBuggyfill.init();</script>
    <script>
	    $('body').on('click', '.js-subscribe', function(){
		    var form = $(this).closest('form');

		    $.ajax({
			    url: './ajax/subscribe.php',
			    type: "POST",
			    data: form.serialize(),
			    success: function(data){
				    $('#mail-form').html(data);
			    }
		    });
		    //console.log('+++');
		    return false;
	    })

    </script>
    </body>
</html>