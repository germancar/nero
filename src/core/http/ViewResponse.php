<?php

namespace Nero\Core\Http;

use Nero\Services\Proxies\Twig;

/**
 * ViewResponse implements the views. Views can be plain php files or twig templates.
 *
 */
class ViewResponse extends Response
{
    /**
     * Which template to send back to the user.
     *
     * @var stdClass
     */
    private $template = null;


    /**
     * Holds names of the plain php files to be used in the response.
     *
     * @var array
     */
    private $views = [];


    /**
     * Constructor.
     *
     * @param string $templateName
     * @param array $data
     * @return void
     */
    public function __construct($templateName = "", $data = [])
    {
	$this->template = new \stdClass;
	$this->template->name = $templateName;
	$this->template->data = $data;
    }


    /**
     * Add a view(plain php file) to the response for rendering.
     *
     * @param string $viewName 
     * @param array $data 
     * @return Nero\Core\Http\ViewResponse
     */
    public function add($viewName,array $data = [])
    {
        //store the view names and their data for rendering
        $this->views[] = ['name' => $viewName, 'data' => $data];

        return $this;
    }


    /**
     * Set the data to be used in a template.
     *
     * @param array $data
     * @return Nero\Core\Http\ViewResponse
     */
    public function with()
    {
	$data = [];

	//merge all data into single array
	foreach (func_get_args() as $arg){
	    $data += $arg;
	}	

	//setup data
	$this->template->data = $data;

	return $this;
    }


    /**
     * Send the response to the user.
     *
     * @return void
     */
    public function send()
    {
        if (!empty($this->views)){
            //lets process the views(plain PHP files) if any are queued for rendering
            foreach ($this->views as $view){
                $this->renderPHPfile($view['name'], $view['data']);
            }
        }
	else{
	    //lets render the twig template
	    $template = str_replace(".", "/", $this->template->name) . '.twig';
	    $data = $this->template->data;
	    echo Twig::render($template, $data);
	}
    }


    /**
     * Render a php file to the page.
     *
     * @param string $view 
     * @param array $data
     * @return void
     */
    private function renderPHPfile($view, $data = [])
    {
        //lets extract the array elements into variables which can be used in the view
        extract($data);

        //include the view
        require_once("../src/app/views/". $view . ".php");
    }
}
