# quartz

Quartz is a usability layer on top of the PHP 5.3 version of the OpenFlame Framework, taking care of common tasks that are left to the application to handle.

## copyright

(c) 2009 - 2011 Damian Bushong

## license

MIT license

## requirements

* PHP 5.3
* PDO (if the db.* OpenFlame Dbal events are used)

## config files

example asset config file (`/data/config/assets.json`)

``` json
{
	"site.path.assets":"/style/",
	"site.assets":{
		"css":{
			"common":"sg.css",
			"common_min":"common.min.css.gz",
		},
		"js":{
			"common":"common.js",
			"common_min":"common.min.js.gz",
			"jquery":"jquery-1.6.1.min.js",
			"jquery_min":"jquery-1.6.1.min.js.gz",
		},
		"image":{
			"logo":"logo.png"
		}
	}
}
```

example route config file (`/data/config/assets.json`)

``` json
{
	"site.routes":{
		"home":{
			"path":"/home/",
			"callback":"::home::"
		},
		"error":{
			"path":"/error/",
			"callback":"::error::"
		},

		# information-only pages
		"about":{
			"path":"about",
			"callback":"::about::"
		},
	},
}
```

example global config file (`data/config/config.json`)

``` json
{
	"db.host":"localhost",
	"db.name":"dbname",
	"db.username":"dbuser",
	"db.password":"passwordhere",
	"db.file":"/data/site.sq3.db",
	"twig.debug":true,
	"site.debug":true,
	"site.use_gzip_assets":true,
	"page.base_url":"/",
}
```

## config settings
* *page.base_url* **default "/"** - The base URL to use for the router, asset manager, and url builder.
* *db.file* - The SQLite file to connect to (only needed if using the sqlite dbms)
* *twig.cache_path* **default "/cache/twig/"** - The directory to use for the Twig cache.
* *twig.template_path* **default "/data/template/"** - The directory to use for loading Twig template files from.
* *twig.debug* **default false** - Enable/disable twig's debug mode.
* *db.host* **default "localhost"** - Database host to connect to (for mysql and pgsql)
* *db.name* - The name of the database to connect to.
* *db.username* - The username to connect to the database with.
* *db.password* - The password to connect to the database with.
* *site.assets* - The asset data to use for the site.
* *site.routes* - The routes to use for the site.
* *site.path.assets* **default "/style/"** - The path to use for all assets.

## provided injectors

* *asset* - The OpenFlame Framework asset manager.
* *asset_proxy* - The OpenFlame Framework asset manager proxy, intended for use in Twig templates.
* *cache* - The OpenFlame Framework cache system, handles loading and storing data in a cache.
* *cache_engine* - The OpenFlame Framework cache engine, override this injector to change the cache engine that the cache system will use.
* *dispatcher* - The OpenFlame Framework event dispatcher.
* *hasher* - The OpenFlame Framework password hasher.
* *header* - The OpenFlame Framework header manager object, used for handling headers to send.
* *input* - The OpenFlame Framework input handler.
* *language* - The OpenFlame Framework localized string manager (used for associating language keys to language strings)
* *language_proxy* - The OpenFlame Framework language object proxy, intended for use in Twig templates.
* *router* - The OpenFlame Framework router, with the `base_url` setup already.
* *seeder* - The OpenFlame Framework random seed string generator.
* *template* - The OpenFlame Framework template variable manager.
* *twig* - The wrapper object for Twig that handles autoloader preparation, template path and cache path settings, among other things.
* *url_builder* - The OpenFlame Framework URL builder, used to generate URL links out of provided patterns.
* *url_proxy* - The OpenFlame Framework URL builder proxy, intended for use in Twig templates.

## provided events

* *exception.setup* **priority 5**: Setup the exception handler
* *debug.enable* **priority 5**: Enable debug mode for your site (displays all notices, errors, etc.)
* *debug.disable* **priority 5**: Disable debug mode for your site.
* *page.hidephp* **priority 0**: Remove the X-Powered-By header from headers to be sent (event `page.headers.snag` MUST be run first)
* *db.sqlite.connect* **priority 0**: Connect to an SQLite database file.
* *db.mysql.connect* **priority 0**: Connect to a MySQL database.
* *db.postgresql.connect* **priority 0**: Connect to a PostGreSQL database.
* *page.routes.load* **priority 5**: Loads routes from the json route file, or load them from the cache if already cached.
* *page.assets.define* **priority 18**: Loads several proxy objects and helpers into the Twig environment for use in templates.
* *page.assets.define* **priority 19**: Enables invalid asset exceptions.
* *page.language.load* **priority 15**: Loads a set of language keys and string.
* *page.headers.snag* **priority 0**: Grabs the headers currently set to be sent.
* *page.headers.send* **priority 10**: Send the headers stored in the header manager.
* *page.execute* **priority -20**: Touches the `$_SERVER` superglobal so the input handler can make use of it.
* *page.execute* **priority 10**: Obtains the current `REQUEST_URI`, runs it against the router, then triggers the callback associated with the matching route.  Also handles redirects and server errors natively.
* *page.display* **priority 10**: Sends page headers, loads the twig template specified by the controller, passes template variables to Twig, then renders and outputs the page.
