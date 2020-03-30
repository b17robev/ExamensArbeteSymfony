<?php

namespace App\Controller;

use App\Entity\Airport;
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

        return $this->json($airports);
    }

    /**
     * @Route("/airports", methods={"Post"})
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $data = $request->request->all();

        $airport = new Airport();

        $airport->setAirportId((int)$data['airport_id']);

        $entityManager->persist($airport);
        $entityManager->flush();

        return new Response("Saved new airport with id " . $airport->getAirportId());
    }

    /**
     * @Route("/airports/{id}", methods={"DELETE"})
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
        $entityManager->flush();

        return new response("Deleted", Response::HTTP_OK);
    }
}
