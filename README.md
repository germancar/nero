# nero
Nero is a PHP MVC lightweight framework

This is a project I started a few weeks back as a means to learn more about php mvc development and php in general. I tried to
copy features that I liked from frameworks like Laravel and CodeIgniter, ie Laravel's routing implementation. This has been a 
really fun learning experience, I hope you can find some value in it.

You can follow my tutorials about this implementation at my blog http://markomihajlovic.blogspot.rs/ if you are interested,
and if you want to contribute you are welcome to do so.  


Install instructions: 

If you clone the repo under some other name (not nero), in order for routing to work you need to change the rewrite base
in your .htaccess file to the root directory where you cloned the repo. 

For example, the RewriteBase line should read like this :
RewriteBase /yourdirectory/public/

Next just run "composer install" to install all the dependencies, and finish it off with "composer dump-autoload -o" to 
generate the autoload files. You should be all set to go, hit the localhost/nero/public/welcome route for splash screen.
