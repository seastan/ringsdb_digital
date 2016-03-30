<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiPrivateController extends Controller {
	/**
	 * Get the description of all the Decks of the authenticated user
	 */
	public function listDecksAction(Request $request) {
		$response = new Response();

		/* @var $decks \AppBundle\Entity\Deck[] */
		$decks = $this->getDoctrine()->getRepository('AppBundle:Deck')->findBy(['user' => $this->getUser()]);

		$dateUpdates = array_map(function($deck) {
			return $deck->getDateUpdate();
		}, $decks);

        if (count($dateUpdates)) {
            $response->setLastModified(max($dateUpdates));
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

		$content = json_encode($decks);

		$response->headers->set('Content-Type', 'application/json');
		$response->setContent($content);

		return $response;
	}

	public function listUserDecksAction($username, Request $request) {
		$response = new Response();

        /* @var $user \AppBundle\Entity\User */
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['username' => $username]);
        if (!$user) {
            $content = json_encode([
                'success' => false,
                'error' => 'This user does not exists.'
            ]);

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($content);

            return $response;
        }

        if (!$user->getIsShareDecks() && $user->getId() != $this->getUser()->getId()) {
            $content = json_encode([
                'success' => false,
                'error' => 'You are not allowed to view this user\'s decks. To get access, you can ask him/her to enable "Share my decks" on their account.'
            ]);

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($content);

            return $response;
        }

        /* @var $decks \AppBundle\Entity\Deck[] */
		$decks = $this->getDoctrine()->getRepository('AppBundle:Deck')->findBy(['user' => $user]);

		$dateUpdates = array_map(function($deck) {
			return $deck->getDateUpdate();
		}, $decks);

        if (count($dateUpdates)) {
            $response->setLastModified(max($dateUpdates));
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

		$content = json_encode($decks);

		$response->headers->set('Content-Type', 'application/json');
		$response->setContent($content);

		return $response;
	}

	/**
	 * Get the description of one Deck of the authenticated user
	 */
	public function loadDeckAction($id, Request $request) {
		$response = new Response();

		/* @var $deck \AppBundle\Entity\Deck */
		$deck = $this->getDoctrine()->getRepository('AppBundle:Deck')->find($id);

        if (!$deck) {
            $content = json_encode([
                'success' => false,
                'error' => 'This deck does not exists.'
            ]);

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($content);

            return $response;
        }

        $user = $deck->getUser();
        if (!$user->getIsShareDecks() && $user != $this->getUser()) {
            $content = json_encode([
                'success' => false,
                'error' => 'You are not allowed to view this deck. To get access, you can ask the deck owner to enable "Share my decks" on their account.'
            ]);

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($content);

            return $response;
        }

		$response->setLastModified($deck->getDateUpdate());
		if ($response->isNotModified($request)) {
			return $response;
		}

		$content = json_encode($deck);

		$response->headers->set('Content-Type', 'application/json');
		$response->setContent($content);

		return $response;
	}
}
