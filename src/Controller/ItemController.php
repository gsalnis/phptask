<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends AbstractController
{
    /**
     * @Route("/item", name="item_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function list(): JsonResponse
    {
        $items = $this->getDoctrine()->getRepository(Item::class)->findBy(['user' => $this->getUser()]);

        $allItems = [];

        foreach ($items as $item) {
            $allItems[] = [
                'id' => $item->getId(),
                'data' => $item->getData(),
                'created_at' => $item->getCreatedAt(),
                'updated_at' => $item->getUpdatedAt(),
            ];
        }

        return $this->json($allItems);
    }

    /**
     * @Route("/item", name="item_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param ItemService $itemService
     * @return JsonResponse
     */
    public function create(Request $request, ItemService $itemService): JsonResponse
    {
        $data = $request->get('data');

        if (empty($data)) {
            return $this->json(['error' => 'No data parameter']);
        }

        $itemService->create($this->getUser(), $data);

        return $this->json([]);
    }

    /**
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        if (empty($id)) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $this->getDoctrine()->getRepository(Item::class)->find($id);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($item);
        $manager->flush();

        return $this->json([]);
    }

    /**
     * @Route("/item/{id}", name="items_update", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id): JsonResponse
    {
        if (empty($id)) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        $manager = $this->getDoctrine()->getManager();
        $items = $manager->getRepository(Item::class)->findBy(['user' => $this->getUser()]);

        foreach ($items as $item) {
            if ($item->getId() === $id) {
                $item->updatedTimestampsOnUpdate();
            }
        }

        $manager->persist($item);
        $manager->flush();

        return $this->json([]);
    }
}
