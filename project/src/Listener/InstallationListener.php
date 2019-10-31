<?php
namespace App\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Checks the presence of the installation.dist file and redirect requests to the installation controller
 */
class InstallationListener
{
    private $bag;
    private $router;
	const INSTALLATION_FILE = "installation.dist";
    public function __construct(ParameterbagInterface $bag, RouterInterface $router) {
      $this->bag = $bag;
      $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event) {
		//if the installation file has not been removed yet
		if(file_exists($this->bag->get("public_dir")."/".$this::INSTALLATION_FILE)) {
			$request_route = $event->getRequest()->attributes->get("_route"); //requested route
	        $install_routes = ["installation", "installation_result"]; //routes used by the installation module
	        $ok_route = (in_array($request_route, array_merge($install_routes, ["_wdt"])) || strpos($request_route, "_profiler") !== false); //_wdt and _profiler_* are routes used by symfony/debug
	        if(!$ok_route && $event->isMasterRequest()) { //requested route can't be accessed
	          $event->setResponse(new RedirectResponse($this->router->generate("installation"))); //redirection to installation module
	          $event->stopPropagation();
	        }
		}
    }
}
