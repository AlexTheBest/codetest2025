<?php
/**
 * WebScraper Class
 * Handles fetching and parsing web content
 */
class WebScraper {
    
    private $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36';
    
    /**
     * Fetch a webpage using cURL
     */
    public function fetchPage($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_ENCODING => '', // This automatically handles gzip/deflate decompression
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        if ($error) {
            return false;
        }
        
        return $response;
    }
    
    /**
     * Check if text is likely navigation/menu text
     */
    private function isNavigationText($text) {
        $navKeywords = [
            'sign in',
            'log in',
            'login',
            'register',
            'subscribe',
            'menu',
            'search',
            'share',
            'follow us',
            'contact',
            'privacy',
            'terms',
            'newsletter',
            'my account',
            'advertisement',
            'sponsored'
        ];
        
        $lowerText = strtolower($text);
        foreach ($navKeywords as $keyword) {
            if ($lowerText === $keyword || strpos($lowerText, $keyword) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Extract article summaries from HTML
     */
    public function extractArticles($html) {
        $articles = [];
        
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        
        // Look for article elements - expanded patterns including Guardian
        $articleQueries = [
            "//article",
            "//*[contains(@class, 'article')]",
            "//*[contains(@class, 'story')]",
            "//*[contains(@class, 'story-block')]",
            "//*[contains(@class, 'post')]",
            "//*[contains(@class, 'content-item')]",
            "//*[contains(@class, 'card') and contains(@class, 'story')]",
            "//*[contains(@class, 'news-item')]",
            "//*[contains(@class, 'story-card')]",
            "//*[contains(@class, 'fc-item')]",
            "//*[contains(@class, 'dcr-') and contains(@class, 'card')]",
            "//li[contains(@class, 'fc-slice__item')]"
        ];
        
        $seen = [];
        
        foreach ($articleQueries as $query) {
            $nodes = $xpath->query($query);
            
            if (!$nodes) continue;
            
            foreach ($nodes as $node) {
                // Extract headline
                $headlineNode = $xpath->query(".//h1 | .//h2 | .//h3 | .//h4 | .//*[contains(@class, 'headline')] | .//*[contains(@class, 'title')]", $node)->item(0);
                $headline = $headlineNode ? trim($headlineNode->textContent) : '';
                
                if (empty($headline) || 
                    strlen($headline) < 10 || 
                    strlen($headline) > 200 ||
                    isset($seen[$headline]) ||
                    $this->isNavigationText($headline)) {
                    continue;
                }
                
                $seen[$headline] = true;
                
                // Extract summary/description
                $summaryNode = $xpath->query(
                    ".//*[contains(@class, 'summary')] | " .
                    ".//*[contains(@class, 'description')] | " .
                    ".//*[contains(@class, 'excerpt')] | " .
                    ".//*[contains(@class, 'standfirst')] | " .
                    ".//*[contains(@class, 'intro')] | " .
                    ".//p[not(ancestor::*[contains(@class, 'comment')]) and not(ancestor::*[contains(@class, 'meta')])]",
                    $node
                )->item(0);
                $summary = $summaryNode ? trim($summaryNode->textContent) : '';
                
    
                

                $articles[] = [
                    'headline' => $headline,
                    'summary' => $summary
                ];
                
            }
        }
        
        return $articles;
    }   
}
?>
