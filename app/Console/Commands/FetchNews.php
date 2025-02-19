<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use App\Models\Authors;

class FetchNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from NewsAPI, BBC News, and The Guardian';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->fetchFromNewsAPI();
        $this->scrapeRSSNews();
        $this->info('News fetched successfully!');
    }

    private function fetchFromNewsAPI()
    {
        $apiKey = env('NEWS_API_KEY');
        // $url = "https://newsapi.org/v2/top-headlines?language=en&apiKey={$apiKey}";
        $url = "https://newsapi.org/v2/top-headlines";
        $page = 1;

        Category::firstOrCreate(
            ['name' => "general"], // Avoid duplicate articles
            ['name' => "general"]
        );
        $author = null;
        Source::updateOrCreate(
            ['name' => "NewsAPI"], // Avoid duplicate articles
            [
                'name' => "NewsAPI",
                'url' => $url
            ]
        );
        $source = Source::where('name', "NewsAPI")->first();
        $generalCategory = Category::where('name', "general")->first();

        do {
            $response = Http::get($url, [
                'apiKey' => $apiKey,
                'language' => 'en',
                'pageSize' => 10, // Limit results to avoid rate limits
                'page' => $page
            ]);

            // $response = Http::get($url);

            if ($response->successful()) {
                $articles = $response->json()['articles'] ?? [];
                $totalResult = $response->json()['totalResults'] ?? 0;

                foreach ($articles as $news) {
                    if (!isset($news['title']) || !isset($news['url']) || !isset($news['source']['name'])) {
                        continue;
                    }
                    if($news['author']) {
                        Authors::updateOrCreate(
                            ['name' => $news['author']], // Avoid duplicate articles
                            ['name' => $news['author']]
                        );
                        $author = Authors::where('name', $news['author'])->first();
                    }
                    if($news['source']['name']) {
                        Source::updateOrCreate(
                            ['name' => $news['source']['name']], // Avoid duplicate articles
                            [
                                'name' => $news['source']['name'],
                                'url' => $url
                            ]
                        );
                        $source = Source::where('name', $news['source']['name'])->first();
                    }
                                    
                    Article::updateOrCreate(
                        ['url' => $news['url']], // Avoid duplicate articles
                        [
                            'title' => $news['title'],
                            'content' => $news['content'] ?? '',
                            'source_id' => $source ? $source->id : 0,
                            'category_id' => $generalCategory->id,
                            'author_id' => $author ? $author->id : null,
                            'img_url' => $news['urlToImage'] ?? "",
                            'published_at' => Carbon::parse($news['publishedAt']),
                        ]
                    );
                }
            }
            $page++;
        } while($totalResult > $page * 10);
    }

    /**
     * Scrape news from The Guardian
     */
    public function scrapeRSSNews(): void
    {
        $sources = [
            'The Guardian' => 'https://www.theguardian.com/international/rss',
            'The New York Times' => 'https://rss.nytimes.com/services/xml/rss/nyt/World.xml'
        ];
        $sourceObj = null;
        Category::firstOrCreate(
            ['name' => "general"], // Avoid duplicate articles
            ['name' => "general"]
        );
        $generalCategory = Category::where('name', "general")->first();

        foreach ($sources as $source => $rssUrl) {
            Source::updateOrCreate(
                ['name' => $source], // Avoid duplicate articles
                [
                    'name' => $source,
                    'url' => $rssUrl
                ]
            );
            $sourceObj = Source::where('name', $source)->first();

            $feed = simplexml_load_file($rssUrl);
            if (!$feed) {
                $this->error("Failed to fetch RSS feed from $source");
                continue;
            }

            foreach ($feed->channel->item as $item) {
                $authorObj = null;
                $category = null;
                // print_r($item);
                $title = (string) $item->title;
                $link = (string) $item->link;
                $pubDate = Carbon::parse((string) $item->pubDate);
                $description = (string) $item->description;
                $author = $item->children('dc', true)->creator;
                $mediaContent = $item->children('media', true)->content;
                $imgUrl = $mediaContent ? (string) $mediaContent->attributes()->url : null;
                $categories = (array) $item->category;
                // print_r([$item->title, (array) $item->category]);
                if($author) {
                    Authors::updateOrCreate(
                        ['name' => $author], // Avoid duplicate articles
                        ['name' => $author]
                    );
                    $authorObj = Authors::where('name', $author)->first();
                }
                if($categories) {
                    Category::updateOrCreate(
                        ['name' => $categories[0]], // Avoid duplicate articles
                        ['name' => $categories[0]]
                    );
                    $category = Category::where('name', $categories[0])->first();
                }
                Article::updateOrCreate(
                    ['url' => $link],
                    [
                        'title' => $title,
                        'content' => $description,
                        'category_id' => $category ? $category->id : $generalCategory->id,
                        'author_id' => $authorObj ? $authorObj->id: "",
                        'img_url' => $imgUrl,
                        'published_at' => $pubDate,
                        'source_id' => $sourceObj->id
                    ]
                );

            }
        }

        // $this->info('News articles scraped successfully!');
    }

    
}