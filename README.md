# Nero 
Nero is a PHP MVC lightweight framework.

Nero is heavily inspired by Laravel. It can be used as a learning tool for newcomers to modern frameworks, as it is a much smaller codebase
than industry ready frameworks, but it is still based on all the principles that are used in modern PHP frameworks. So you can easily browse
the code and learn in the process. There is a full documentation site in the making which will showcase all the features, but to summarize the features are as follow:

- Easy modular routing(CodeIgniter or Laravel style routers)
- Database interaction (ORM models, Query Builder, raw queries)
- Dependency injection on controller methods, request filters, bootstrappers and terminators
- IoC container
- Views can be plain PHP files or Twig templates
- Exception handling displays formated view with information for debugging

If you are interested, you can follow my tutorials about this implementation at my blog http://markomihajlovic.blogspot.rs/
and if you want to contribute you are welcome to do so.  


Install instructions: 

Nero is based on LAMP stack and it uses Apache mod_rewrite, so all you need to do for your project is change the rewrite base for your
your own project location to reflect the name of your project. Host(localhost) should serve from nero("your project name")/public directory.
Modify public/.htaccess file if needed - RewriteBase rule.

Next just run "composer install" to install all the dependencies, and finish it off with "composer dump-autoload -o" to 
generate the autoload files. You should be all set to go, hit the nero("your project name")/public route for splash screen.
