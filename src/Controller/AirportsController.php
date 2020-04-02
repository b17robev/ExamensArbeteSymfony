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
     */
    public function index()
    {
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

        $before = microtime(true);
        $airport = $this->getDoctrine()
            ->getRepository(Airport::class)
            ->find($id);
        $after = microtime(true);

        $url = "http://localhost:8080/scrapper/index.php";
        $result = $after - $before . "\n";

        $this->httpPost($url, $result, "show");

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

        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);

        $url = "http://localhost:8080/scrapper/index.php";
        $result = $after - $before . "\n";

        $this->httpPost($url, $result, "update");

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

        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);

        $url = "http://localhost:8080/scrapper/index.php";
        $result = $after - $before . "\n";

        $this->httpPost($url, $result, "store");

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

        $before = microtime(true);
        $entityManager->flush();
        $after = microtime(true);

        $url = "http://localhost:8080/scrapper/index.php";
        $result = $after - $before . "\n";

        $this->httpPost($url, $result, "destroy");

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
