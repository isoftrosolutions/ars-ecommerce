<?php
/**
 * Category Controller
 * Public category listing and banners.
 *
 * @route /categories, /banners
 * @auth public
 */

class CategoryController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * GET /categories
     */
    public function index($params)
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name, c.slug,
                   COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id, c.name, c.slug
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add icon/image fields (null by default — can be extended later)
        foreach ($categories as &$cat) {
            $cat['icon'] = null;
            $cat['image'] = null;
        }
        unset($cat);

        json_success($categories);
    }

    /**
     * GET /banners
     */
    public function banners($params)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, image, title, subtitle, link_type, link_value, sort_order
            FROM banners
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute();
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($banners as &$banner) {
            $banner['image'] = banner_image_url($banner['image']);
        }
        unset($banner);

        json_success($banners);
    }
}
