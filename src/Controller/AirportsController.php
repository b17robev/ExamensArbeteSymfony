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
        $airports = $this->getDoctrine()
            ->getRepository(Airport::class)
            ->findAll();
        $rep = $this->getDoctrine()
            ->getRepository(Airport::class);
        if($amount = $request->attributes->get('amount')) {
            $rep->take($amount);
        }

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

        $base_mem = memory_get_peak_usage();
        $before = microtime(true);
        $airport = $this->getDoctrine()
            ->getRepository(Airport::class)
            ->find($id);
        $after = microtime(true);
        $total_mem = memory_get_peak_usage();

        $data[] = $after - $before;
        $data[] = $total_mem - $base_mem;

        $result = implode(',', $data) . "\n";

        $this->httpPost($this->getParameter('app.scraper_url'), $result, "show");

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

        $entityManager->persist($airport);

        $data = [];

        $base_mem = memory_get_peak_usage();
        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);
        $total_mem = memory_get_peak_usage();

        $data[] = $after - $before;
        $data[] = $total_mem - $base_mem;

        $result = implode(',', $data) . "\n";

        $this->httpPost($this->getParameter('app.scraper_url'), $result, "update");


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

        $entityManager->persist($airport);

        $data = [];

        $base_mem = memory_get_peak_usage();
        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);
        $total_mem = memory_get_peak_usage();

        $data[] = $after - $before;
        $data[] = $total_mem - $base_mem;

        $result = implode(',', $data) . "\n";

        $this->httpPost($this->getParameter('app.scraper_url'), $result, "store");

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

        $base_mem = memory_get_peak_usage();
        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);
        $total_mem = memory_get_peak_usage();

        $data[] = $after - $before;
        $data[] = $total_mem - $base_mem;

        $result = implode(',', $data) . "\n";

        $this->httpPost($this->getParameter('app.scraper_url'), $result, "destroy");

        return new Response('Deleted', 200);
    }

    function httpPost($url, $data, $method)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "$method=$data");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

}
