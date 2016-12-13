<?php

namespace Nero\Core\Http;

/********************************************************************
 * ViewResponse implements the needed functionality for displaying
 * views to the users. It implements the abstract send method for
 * rendering views to the browser.
 ********************************************************************/
class ViewResponse extends Response
{
    private $template = null;
    private $views = [];


    public function __construct($templateName = "", $data = [])
    {
	//setup the template 
	$this->template = new \stdClass;
	$this->template->name = $templateName;
	$this->template->data = $data;
    }


    /**
     * Add a view to the response for rendering
     *
     * @param string $viewName 
     * @param array $data 
     * @return Nero\Core\Http\Response
     */
    public function add($viewName,array $data = [])
    {
        //store the view names and their data for rendering
        $this->views[] = ['name' => $viewName, 'data' => $data];

        //lets return the response object so methods can be chained
        return $this;
    }


    public function with(array $data)
    {
	//just set the template data
	$this->template->data = $data;

	return $this;
    }


    /**
     * Send the response back to the user
     *
     */
    public function send()
    {
        if(!empty($this->views)){
            //lets process the views(plain PHP files) if any are queued for rendering
            foreach($this->views as $view){
                $this->renderPHPfile($view['name'], $view['data']);
            }
        }
	else{
	    //get the data
	    $template = $this->template->name . '.twig';
	    $data = $this->template->data;

	    //echo the template
	    echo container('Twig')->render($template, $data);
	}
    }


    /**
     * Render a view to the page
     *
     * @param string $view 
     * @param array $data
     * @return void
     */
    private function renderPHPfile($view, $data = [])
    {
        //lets extract the array keys into variables which can be used in the view
        extract($data);

        //include the view
        require_once("../src/app/views/". $view . ".php");
    }
}
