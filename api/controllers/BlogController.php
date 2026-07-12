<?php
require_once __DIR__ . '/../config/database.php';

class BlogController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getRecentPosts($limit = 9) {
        if (!$this->db) return $this->getStaticFallbackPosts();
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC LIMIT ?");
            // Workaround for PDO limit parameter binding
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($posts)) {
                return $this->getStaticFallbackPosts();
            }
            return $posts;
        } catch (PDOException $e) {
            return $this->getStaticFallbackPosts();
        }
    }

    private function getStaticFallbackPosts() {
        return [
            [
                'id' => 1,
                'category' => 'Hair Care',
                'title' => 'The Science of Perfect Highlights',
                'excerpt' => 'Discover how our master colorists achieve the perfect balance of tone and dimension without compromising hair health.',
                'image_url' => 'https://images.unsplash.com/photo-1562322140-8baeececf3df?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'published_at' => '2023-10-15 10:00:00'
            ],
            [
                'id' => 2,
                'category' => 'Grooming',
                'title' => 'The Modern Gentleman\'s Beard Guide',
                'excerpt' => 'From trimming techniques to the best oils. Keep your beard looking sharp and well-maintained year-round.',
                'image_url' => 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'published_at' => '2023-10-05 14:30:00'
            ],
            [
                'id' => 3,
                'category' => 'Skincare',
                'title' => 'Winter Hydration Rituals',
                'excerpt' => 'Protect your skin barrier during the colder months with our curated spa treatments and at-home routines.',
                'image_url' => 'https://images.unsplash.com/photo-1616394584738-fc6e612e71b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'published_at' => '2023-09-28 09:15:00'
            ]
        ];
    }
}
