<?php
/**
 * ContentAnalyser Class
 * Analyses text content to extract keywords and identify trends
 */
class ContentAnalyser {

    // Category articles associated with trends
    private $trendCategories = [
        'politics' => ['government', 'parliament', 'minister', 'election', 'politics', 'political', 'policy', 'senate', 'vote', 'debate'],
        'business' => ['business', 'economy', 'market', 'company', 'corporate', 'profit', 'stock', 'finance', 'industry', 'trade'],
        'technology' => ['technology', 'digital', 'tech', 'software', 'internet', 'cyber', 'online', 'data', 'innovation', 'smartphone'],
        'health' => ['health', 'medical', 'hospital', 'doctor', 'patient', 'disease', 'treatment', 'vaccine', 'pandemic', 'healthcare'],
        'sport' => ['sport', 'game', 'team', 'player', 'match', 'championship', 'coach', 'league', 'football', 'cricket'],
        'entertainment' => ['entertainment', 'movie', 'music', 'celebrity', 'actor', 'film', 'show', 'concert', 'television'],
        'environment' => ['climate', 'environment', 'environmental', 'green', 'energy', 'carbon', 'pollution', 'sustainability', 'renewable', 'emissions'],
        'crime' => ['crime', 'police', 'court', 'arrest', 'charged', 'investigation', 'criminal', 'murder', 'assault', 'theft']
    ];


    // Common English stop words to filter out, simple method without using library
    private $stopWords = [
        // Articles
        'a', 'an', 'the',
        
        // Conjunctions
        'and', 'but', 'or', 'nor', 'for', 'yet', 'so',
        
        // Prepositions
        'as', 'at', 'by', 'in', 'of', 'on', 'to', 'up', 'about', 'above', 'across',
        'after', 'against', 'along', 'among', 'around', 'before', 'behind', 'below',
        'beneath', 'beside', 'between', 'beyond', 'down', 'during', 'except', 'from',
        'inside', 'into', 'near', 'off', 'out', 'outside', 'over', 'since', 'through',
        'throughout', 'toward', 'under', 'underneath', 'until', 'upon', 'with', 'within',
        'without',
        
        // Pronouns
        'i', 'you', 'he', 'she', 'it', 'we', 'they', 'them', 'their', 'theirs',
        'him', 'his', 'her', 'hers', 'its', 'our', 'ours', 'your', 'yours',
        'me', 'my', 'mine', 'us', 'who', 'whom', 'whose', 'which', 'what',
        'this', 'that', 'these', 'those',
        
        // Common verbs
        'is', 'am', 'are', 'was', 'were', 'be', 'been', 'being',
        'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing',
        'will', 'would', 'should', 'could', 'can', 'may', 'might', 'must',
        'shall', 'ought',
        
        // Common adverbs
        'not', 'no', 'yes', 'very', 'too', 'also', 'just', 'only', 'even',
        'now', 'than', 'then', 'here', 'there', 'where', 'when', 'why', 'how',
        'well', 'still', 'back', 'again', 'already', 'always', 'never',
        'often', 'sometimes', 'usually', 'really', 'quite', 'rather',
        'almost', 'nearly', 'hardly', 'barely', 'ever', 'today', 'yesterday',
        
        // Common adjectives
        'all', 'any', 'each', 'every', 'some', 'few', 'many', 'much',
        'more', 'most', 'other', 'another', 'such', 'own', 'same', 'different',
        'new', 'old', 'first', 'last', 'next', 'previous', 'following',
        'good', 'bad', 'best', 'better', 'big', 'small', 'large', 'little',
        'long', 'short', 'high', 'low', 'great', 'less', 'least',
        
        // Other common words
        'said', 'says', 'saying', 'say', 'told', 'tell', 'asked', 'ask',
        'get', 'got', 'gets', 'getting', 'got', 'gotten',
        'make', 'makes', 'made', 'making', 'take', 'takes', 'took', 'taken', 'taking',
        'come', 'comes', 'came', 'coming', 'go', 'goes', 'went', 'going', 'gone',
        'see', 'sees', 'saw', 'seen', 'seeing', 'look', 'looks', 'looked', 'looking',
        'know', 'knows', 'knew', 'known', 'knowing', 'think', 'thinks', 'thought', 'thinking',
        'want', 'wants', 'wanted', 'wanting', 'need', 'needs', 'needed', 'needing',
        'give', 'gives', 'gave', 'given', 'giving', 'find', 'finds', 'found', 'finding',
        'use', 'uses', 'used', 'using', 'work', 'works', 'worked', 'working',
        'call', 'calls', 'called', 'calling', 'try', 'tries', 'tried', 'trying',
        'feel', 'feels', 'felt', 'feeling', 'leave', 'leaves', 'left', 'leaving',
        'put', 'puts', 'putting', 'mean', 'means', 'meant', 'meaning',
        'keep', 'keeps', 'kept', 'keeping', 'let', 'lets', 'letting',
        'begin', 'begins', 'began', 'begun', 'beginning', 'seem', 'seems', 'seemed', 'seeming',
        'help', 'helps', 'helped', 'helping', 'show', 'shows', 'showed', 'shown', 'showing',
        'turn', 'turns', 'turned', 'turning', 'start', 'starts', 'started', 'starting',
        'run', 'runs', 'ran', 'running', 'move', 'moves', 'moved', 'moving',
        'live', 'lives', 'lived', 'living', 'believe', 'believes', 'believed', 'believing',
        'bring', 'brings', 'brought', 'bringing', 'happen', 'happens', 'happened', 'happening',
        'write', 'writes', 'wrote', 'written', 'writing', 'provide', 'provides', 'provided', 'providing',
        'sit', 'sits', 'sat', 'sitting', 'stand', 'stands', 'stood', 'standing',
        'lose', 'loses', 'lost', 'losing', 'pay', 'pays', 'paid', 'paying',
        'meet', 'meets', 'met', 'meeting', 'include', 'includes', 'included', 'including',
        'continue', 'continues', 'continued', 'continuing', 'set', 'sets', 'setting',
        'learn', 'learns', 'learned', 'learnt', 'learning', 'change', 'changes', 'changed', 'changing',
        'lead', 'leads', 'led', 'leading', 'understand', 'understands', 'understood', 'understanding',
        'watch', 'watches', 'watched', 'watching', 'follow', 'follows', 'followed', 'following',
        'stop', 'stops', 'stopped', 'stopping', 'create', 'creates', 'created', 'creating',
        'speak', 'speaks', 'spoke', 'spoken', 'speaking', 'read', 'reads', 'reading',
        'allow', 'allows', 'allowed', 'allowing', 'add', 'adds', 'added', 'adding',
        'spend', 'spends', 'spent', 'spending', 'grow', 'grows', 'grew', 'grown', 'growing',
        'open', 'opens', 'opened', 'opening', 'walk', 'walks', 'walked', 'walking',
        'win', 'wins', 'won', 'winning', 'offer', 'offers', 'offered', 'offering',
        'remember', 'remembers', 'remembered', 'remembering', 'love', 'loves', 'loved', 'loving',
        'consider', 'considers', 'considered', 'considering', 'appear', 'appears', 'appeared', 'appearing',
        'buy', 'buys', 'bought', 'buying', 'wait', 'waits', 'waited', 'waiting',
        'serve', 'serves', 'served', 'serving', 'die', 'dies', 'died', 'dying',
        'send', 'sends', 'sent', 'sending', 'expect', 'expects', 'expected', 'expecting',
        'build', 'builds', 'built', 'building', 'stay', 'stays', 'stayed', 'staying',
        'fall', 'falls', 'fell', 'fallen', 'falling', 'cut', 'cuts', 'cutting',
        'reach', 'reaches', 'reached', 'reaching', 'kill', 'kills', 'killed', 'killing',
        'remain', 'remains', 'remained', 'remaining', 'suggest', 'suggests', 'suggested', 'suggesting',
        'raise', 'raises', 'raised', 'raising', 'pass', 'passes', 'passed', 'passing',
        'sell', 'sells', 'sold', 'selling', 'require', 'requires', 'required', 'requiring',
        'report', 'reports', 'reported', 'reporting', 'decide', 'decides', 'decided', 'deciding',
        'pull', 'pulls', 'pulled', 'pulling',
        
        // Time-related
        'year', 'years', 'month', 'months', 'week', 'weeks', 'day', 'days',
        'hour', 'hours', 'minute', 'minutes', 'time', 'times',
        
        // Misc common words
        'thing', 'things', 'people', 'person', 'way', 'ways', 'man', 'men',
        'woman', 'women', 'child', 'children', 'life', 'world', 'hand', 'hands',
        'part', 'parts', 'place', 'places', 'case', 'cases', 'point', 'points',
        'fact', 'facts', 'number', 'numbers', 'group', 'groups', 'problem', 'problems',
        'something', 'nothing', 'anything', 'everything', 'someone', 'anyone', 'everyone',
        'nobody', 'somebody', 'anybody', 'everybody', 'somewhere', 'anywhere', 'everywhere',
        'nowhere', 'however', 'therefore', 'thus', 'hence', 'otherwise', 'meanwhile',
        'furthermore', 'moreover', 'nevertheless', 'nonetheless', 'indeed', 'perhaps',
        'maybe', 'probably', 'possibly', 'certainly', 'definitely', 'exactly', 'especially',
        'particularly', 'specifically', 'generally', 'usually', 'typically', 'basically',
        'essentially', 'actually', 'literally', 'simply', 'merely', 'purely',
        'like', 
        
        // News-specific filler words
        'topic', 'story', 'news', 'article', 'headline', 'according', 'via',
        'source', 'sources', 'report', 'reports', 'coverage', 'update', 'updates',
        'breaking', 'latest', 'live', 'exclusive', 'full', 'video', 'photo', 'image',
        'click', 'read', 'watch', 'listen', 'share', 'comment', 'comments',
        'more', 'less', 'view', 'views', 'page', 'pages', 'site', 'website',
        'link', 'links', 'content', 'published', 'posted', 'updated',
        
        // Website navigation words
        'home', 'menu', 'search', 'subscribe', 'sign', 'login', 'logout',
        'register', 'account', 'profile', 'settings', 'privacy', 'terms',
        'contact', 'about', 'section', 'category', 'advertisement', 'sponsored'
    ];

    private $groupedKeywords = [
        // Location variations
        'australian' => 'australia',
        'aussie' => 'australia',
        'aussies' => 'australia',
        'americans' => 'america',
        'american' => 'america',
        'british' => 'britain',
        'chinese' => 'china',
        
        // Plural to singular
        'years' => 'year',
        'months' => 'month',
        'weeks' => 'week',
        'days' => 'day',
        'hours' => 'hour',
        'children' => 'child',
        'countries' => 'country',
        'cities' => 'city',
        'companies' => 'company',
        'markets' => 'market',
        'schools' => 'school',
        'hospitals' => 'hospital',
        'students' => 'student',
        'teachers' => 'teacher',
        'workers' => 'worker',
        'players' => 'player',
        'teams' => 'team',
        'games' => 'game',
        'officials' => 'official',
        'ministers' => 'minister',
        'ministers' => 'minister',
        'businesses' => 'business',
        'attacks' => 'attack',
        'issues' => 'issue',
        'cases' => 'case',
        'deaths' => 'death',
        'reports' => 'report',
        'warnings' => 'warning',
        'rules' => 'rule',
        'laws' => 'law',
        'changes' => 'change',
        'plans' => 'plan',
        'claims' => 'claim',
        'calls' => 'call',
        'families' => 'family',
        'parents' => 'parent',
        'victims' => 'victim',
        'charges' => 'charge',
        'records' => 'record',
        
        // Common variations
        'govt' => 'government',
        'gov' => 'government',
        'intl' => 'international',
        'natl' => 'national',
        'govt' => 'government',
        'pic' => 'picture',
        'pics' => 'picture',
        'vid' => 'video',
        'vids' => 'video',
        'info' => 'information',
        'tech' => 'technology',
        'biz' => 'business',
        'corp' => 'corporation',
        'univ' => 'university',
        'dept' => 'department',
        'pres' => 'president',
        'rep' => 'representative',
        'sen' => 'senator',
        'min' => 'minister',
        'prof' => 'professor',
        'dr' => 'doctor',
        
        // Action variations
        'announced' => 'announce',
        'announces' => 'announce',
        'announcing' => 'announce',
        'revealed' => 'reveal',
        'reveals' => 'reveal',
        'revealing' => 'reveal',
        'confirmed' => 'confirm',
        'confirms' => 'confirm',
        'confirming' => 'confirm',
        'warned' => 'warn',
        'warns' => 'warn',
        'warning' => 'warn',
        'accused' => 'accuse',
        'accuses' => 'accuse',
        'accusing' => 'accuse',
        'charged' => 'charge',
        'charges' => 'charge',
        'charging' => 'charge',
        'arrested' => 'arrest',
        'arrests' => 'arrest',
        'arresting' => 'arrest',
        'investigated' => 'investigate',
        'investigates' => 'investigate',
        'investigating' => 'investigate',
        'investigation' => 'investigate',
        'investigations' => 'investigate'
    ];
    
    /**
     * Extract keywords from text
     */
    public function extractKeywords($text) {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove special characters and extra spaces
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Split into words
        $words = explode(' ', $text);
        
        // Count word frequency
        $wordCount = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            
            // Skip stop words, short words, and numbers
            if (strlen($word) < 4 || in_array($word, $this->stopWords) || is_numeric($word)) {
                continue;
            }

            // Grouping words, library or llm would be better at this
            if (isset($this->groupedKeywords[$word])) {
                $word = $this->groupedKeywords[$word];
            }
            
            if (!isset($wordCount[$word])) {
                $wordCount[$word] = 0;
            }
            
            $wordCount[$word]++;
        }
        
        // Sort by frequency
        arsort($wordCount);
        
        // Get top keywords
        $keywords = [];
        $count = 0;
        
        foreach ($wordCount as $word => $frequency) {            
            $keywords[] = [
                'keyword' => $word,
                'frequency' => $frequency
            ];
            
            $count++;
        }
        
        return $keywords;
    }
    
    /**
     * Identify trends from keywords
     */
    public function identifyTrends($keywords) {
        $trends = [];
        

        
        $categoryScores = [];
        
        foreach ($keywords as $keywordData) {
            $keyword = is_array($keywordData) ? $keywordData['keyword'] : $keywordData;
            $frequency = is_array($keywordData) ? $keywordData['frequency'] : 1;

            
            foreach ($this->trendCategories as $category => $terms) {
                foreach ($terms as $term) {
                    if (stripos($keyword, $term) !== false || stripos($term, $keyword) !== false) {
                        if (!isset($categoryScores[$category])) {
                            $categoryScores[$category] = 0;
                        }
                        $categoryScores[$category] += $frequency;
                    }
                }
            }
        }
        
        // Sort by score
        arsort($categoryScores);
        
        // Build trends array
        foreach ($categoryScores as $category => $score) {
            if ($score > 0) {
                $trends[] = [
                    'category' => $category,
                    'score' => $score
                ];
            }
        }
        
        return $trends;
    }
    
}
?>
