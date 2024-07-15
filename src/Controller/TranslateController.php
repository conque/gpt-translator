<?php
// src/Controller/TranslateController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslateController extends AbstractController
{
    private $client;
    private $openaiApiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->openaiApiKey = 'YOUR_OPENAI_API_KEY'; // API anahtarınızı buraya ekleyin
    }

    #[Route('/', name: 'translate')]
    public function translate(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('text', TextType::class, ['label' => 'Translate Text'])
            ->add('target_language', TextType::class, ['label' => 'Target Language'])
            ->add('translate', SubmitType::class, ['label' => 'Translate'])
            ->getForm();

        $form->handleRequest($request);
        $translatedText = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $translatedText = $this->translateText($data['text'], $data['target_language']);
        }

        return $this->render('translate/index.html.twig', [
            'form' => $form->createView(),
            'translatedText' => $translatedText,
        ]);
    }

    private function translateText($text, $targetLanguage)
    {
        $prompt = "Translate the following text to {$targetLanguage}: {$text}";

        $response = $this->client->request('POST', 'https://api.openai.com/v1/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'text-davinci-003',
                'prompt' => $prompt,
                'max_tokens' => 60,
            ],
        ]);

        $content = $response->toArray();
        return $content['choices'][0]['text'] ?? 'Translation failed.';
    }
}

