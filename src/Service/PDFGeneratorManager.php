<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PDFGeneratorManager
{
    private Environment $twig;
    private KernelInterface $kernel;
    private UrlGeneratorInterface $router;

    public function __construct(Environment $twig, KernelInterface $kernel, UrlGeneratorInterface $router)
    {
        $this->twig = $twig;
        $this->kernel = $kernel;
        $this->router = $router;
    }

    /**
     * Generate a PDF Response from a twig template.
     *
     * @param string $template twig path (eg. 'bulletin/pdf.html.twig')
     * @param array $context data passed to twig
     * @param string $filename output filename (without path)
     * @param bool $inline true -> show in browser, false -> force download
     */
    public function renderPdfResponse(string $template, array $context = [], string $filename = 'document.pdf', bool $inline = true): Response
    {
        // Ensure assets use absolute urls so images work
        // Provide base_url (scheme + host) if not present in context
        if (!isset($context['base_url'])) {
            // best-effort: local development host will be provided at render time;
            // if you call from CLI make sure to set base_url explicitly in context
            $context['base_url'] = $this->getBaseUrl();
        }

        $html = $this->twig->render($template, $context);

        // Dompdf options
        $options = new Options();
        $options->set('isRemoteEnabled', true); // allow loading images via absolute URLs
        $options->set('defaultFont', 'DejaVu Sans'); // good for unicode (accented characters)

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // A4 portrait and margins: adapt if needed
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        $pdfOutput = $dompdf->output();

        $response = new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"',
        ]);

        return $response;
    }

    private function getBaseUrl(): string
    {
        // Attempt to detect project public directory path for file:// loading fallback
        // If your images are in public/assets/images/logo.png, you can use an absolute local file path.
        // However Dompdf can load http(s) resources when isRemoteEnabled = true and using absolute URLs.
        // For simplicity we return an empty string — prefer passing base_url from controller using Request object.
        return '';
    }
}
