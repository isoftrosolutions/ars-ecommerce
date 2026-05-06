<?php
/**
 * Public Team Members Controller
 * Returns active team members for public display (about page, etc.)
 */
class TeamMembersController extends BaseController {

    public function handleRequest($method, $action) {
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
            return;
        }

        $stmt = $this->executeQuery(
            "SELECT name, role, position, profile_image, fb_link, bio
             FROM team_members
             WHERE is_active = 1
             ORDER BY display_order ASC, created_at ASC"
        );

        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($members as &$member) {
            if (empty($member['profile_image'])) {
                $member['profile_image'] = '/public/assets/img/default-avatar.png';
            }
        }

        Response::success(['members' => $members]);
    }
}
?>
