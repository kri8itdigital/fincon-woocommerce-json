# Fincon Woocommerce
### Current Version: 2.6
Connects your [Fincon](https://fincon.co.za/) accounting system JSON API to Woocommerce.

For more information, requirements and setup kindly browse the [WIKI](https://github.com/kri8itdigital/fincon-woocommerce-json/wiki).

Developed and Maintained by [Kri8it](https://kri8it.com/).


### As of version 2.6
Filter has been added to use Fincon's Alternate Extension. 
```
add_filter('fincon_woocommerce_alternate_extention', 'use_fincon_alternate', 999, 1);
function use_fincon_alternate($_USE_ALTERNATE){

	$_USE_ALTERNATE = 1;

	return $_USE_ALTERNATE;

}
```

This code needs to be placed in the functions.php file of your active theme.

**It has been done in such a way that current iterations are safe and that it cannot be enabled by accident.**