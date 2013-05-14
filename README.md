### Installation ###

Add following to composer.json

	"toyfoundry/twigcache-bundle": "dev-master"

run

	php composer.phar update toyfoundry/twigcache-bundle

add to AppKernel.php

	 new ToyFoundry\TwigCacheBundle\TfTwigCacheBundle(),

### Configuration ###
Add following to your config.yml

	tf_twig_cache:
    	memcached_servers:
        	- {host: '127.0.0.1', port: 11211}

### Usage ###

	{% cache 'segment/name' ttl %}
	    {# heavy lifting template stuff here, include/render other partials etc #}
	{% endcache %}