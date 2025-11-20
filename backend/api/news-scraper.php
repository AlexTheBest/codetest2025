<?php
// News Scraper API Endpoint
require_once '../config/config.php';
require_once '../utils/WebScraper.php';
require_once '../utils/ContentAnalyser.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get the URL from request
    $url = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $url = isset($_GET['url']) ? $_GET['url'] : null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $url = isset($data['url']) ? $data['url'] : null;
    }
    
    if (!$url) {
        throw new Exception('URL parameter is required');
    }
    
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL provided');
    }
    
    // Initialise scraper and analyser
    $scraper = new WebScraper();
    $analyser = new ContentAnalyser();
    
    // Fetch the webpage content
    $html = $scraper->fetchPage($url);
    
    if (!$html) {
        throw new Exception('Failed to fetch the webpage');
    }
    
    // Parse the content
    $articles = $scraper->extractArticles($html);
    
    // Analyse content for trends and keywords
    $allText = implode(' ', array_merge(
        array_column($articles, 'headline'),
        array_column($articles, 'summary')
    ));
    
    $keywords = $analyser->extractKeywords($allText);
    $trends = $analyser->identifyTrends($keywords);
    
    // Build response
    $response = [
        'success' => true,
        'url' => $url,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'articles' => $articles,
            'keywords' => $keywords,
            'trends' => $trends
        ],
        'stats' => [
            'total_articles' => count($articles),
            'total_keywords' => count($keywords),
            'total_trends' => count($trends)
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo json_encode($response);
}
?>
