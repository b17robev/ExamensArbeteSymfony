<?php

namespace App\Controller;

use App\Entity\Airport;
use http\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AirportsController extends AbstractController
{
    /**
     * @Route("/airports", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        if($amount = $request->get('amount')) {
            $data = [];

            $base_mem = memory_get_usage();
            $before = microtime(true);
            $airports = $this->getDoctrine()
                ->getRepository(Airport::class)
                ->take($amount);
            $after = microtime(true);
            $total_mem = memory_get_usage();

            $data[] = ($after - $before) * 1000; //Convert to ms
            $data[] = ($total_mem - $base_mem) / 1024; //Convert to kb

            $result = implode(',', $data) . "\n";

            $this->writeToFile($this->getParameter('app.root'), "index", $result);

            return new JsonResponse($airports);
        }

        $airports = $this->getDoctrine()
            ->getRepository(Airport::class)
            ->findAll();

        return $this->render("airports/index.html.twig", [
            'airports' => $airports
        ]);
    }

    /**
     * @Route("/airports/{id}", methods={"GET"}, name="airport_show")
     * @param $id
     * @return Response
     */
    public function show($id)
    {
        $data = [];

        $base_mem = memory_get_usage();
        $before = microtime(true);
        $airport = $this->getDoctrine()
            ->getRepository(Airport::class)
            ->find($id);
        $after = microtime(true);
        $total_mem = memory_get_usage();

        $data[] = ($after - $before) * 1000; //Convert to ms
        $data[] = ($total_mem - $base_mem) / 1024; //Convert to kb

        $result = implode(',', $data) . "\n";

        $this->writeToFile($this->getParameter('app.root'), "show", $result);

        return $this->render("airports/show.html.twig", [
            'airport' => $airport
        ]);
    }

    /**
     * @Route("/airports/{id}", methods={"PATCH"}, name="airport_update")
     * @param $id
    * @param Request $request
    * @return Response
    */
    public function update(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();

        $airport = $this->getDoctrine()->getRepository(Airport::class)->find($id);

        $airport->setName($data['name']);
        $airport->setCity($data['city']);
        $airport->setCountry($data['country']);
        $airport->setIata($data['iata']);
        $airport->setIcao($data['icao']);
        $airport->setLatitude((float)$data['latitude']);
        $airport->setLongitude((float)$data['longitude']);
        $airport->setAltitude((int)$data['altitude']);
        $airport->setTimezone($data['timezone']);
        $airport->setTzDatabaseTimeZone($data['tz_database_time_zone']);
        $airport->setType($data['type']);
        $airport->setSource($data['source']);
        $airport->setLocation($data['location']);

        $entityManager->persist($airport);

        $data = [];

        $base_mem = memory_get_usage();
        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);
        $total_mem = memory_get_usage();

        $data[] = ($after - $before) * 1000; //Convert to ms
        $data[] = ($total_mem - $base_mem) / 1024; //Convert to kb

        $result = implode(',', $data) . "\n";

        $this->writeToFile($this->getParameter('app.root'), "update", $result);

        return new JsonResponse($airport);
    }

    /**
     * @Route("/airports", methods={"Post"}, name="airport_store")
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();

        $airport = new Airport();

        $airport->setName($data['name']);
        $airport->setCity($data['city']);
        $airport->setCountry($data['country']);
        $airport->setIata($data['iata']);
        $airport->setIcao($data['icao']);
        $airport->setLatitude((float)$data['latitude']);
        $airport->setLongitude((float)$data['longitude']);
        $airport->setAltitude((int)$data['altitude']);
        $airport->setTimezone($data['timezone']);
        $airport->setTzDatabaseTimeZone($data['tz_database_time_zone']);
        $airport->setType($data['type']);
        $airport->setSource($data['source']);
        $airport->setLocation($data['location']);

        $entityManager->persist($airport);

        $data = [];

        $base_mem = memory_get_usage();
        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);
        $total_mem = memory_get_usage();

        $data[] = ($after - $before) * 1000; //Convert to ms
        $data[] = ($total_mem - $base_mem) / 1024; //Convert to kb

        $result = implode(',', $data) . "\n";

        $this->writeToFile($this->getParameter('app.root'), "store", $result);

        return new Response("Saved new airport with id " . $airport->getAirportId());
    }

    /**
     * @Route("/airports/{id}", methods={"DELETE"}, name="airport_destroy")
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $airport = $this->getDoctrine()
            ->getRepository(Airport::class)
            ->find($id);

        $entityManager->remove($airport);

        $data = [];

        $base_mem = memory_get_usage();
        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);
        $total_mem = memory_get_usage();

        $data[] = ($after - $before) * 1000; //Convert to ms
        $data[] = ($total_mem - $base_mem) / 1024; //Convert to kb

        $result = implode(',', $data) . "\n";

        $this->writeToFile($this->getParameter('app.root'), "destroy", $result);

        return new Response('Deleted', 200);
    }

    function writeToFile($root, $file, $content)
    {
        $measurementsPath = $root . DIRECTORY_SEPARATOR. 'measurements' . DIRECTORY_SEPARATOR;
        $actions = ['index', 'update', 'store', 'destroy', 'show'];

        if(!file_exists($root . DIRECTORY_SEPARATOR. 'measurements')) {
            mkdir($root . DIRECTORY_SEPARATOR. 'measurements');
        }

        $files = array_diff(scandir($measurementsPath), array('.', '..'));
        if(!count($files)) {
            foreach($actions as $action)
            {
                if(!is_file($measurementsPath . $action . '.txt'))
                {
                    file_put_contents($measurementsPath . $action . '.txt', '');
                }
            }
        }

        $outputFile = $measurementsPath . $file . '.txt';

        $fp=fopen($outputFile,"a");
        fputs ($fp, $content);
        fclose ($fp);
    }

}
