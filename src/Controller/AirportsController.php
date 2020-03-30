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
     * @Route("/airports/{id}", methods={"GET"})
     * @param $id
     * @return Response
     */
    public function show($id)
    {
        $airport = $this->getDoctrine()
            ->getRepository(Airport::class)
            ->find($id);

        return new JsonResponse($airport);
    }

    /**
     * @Route("/airports/{id}", methods={"PATCH"})
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
        $entityManager->persist($airport);

        $entityManager->flush();

        return new JsonResponse($airport);
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
        $airport->setName($data['name']);

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
