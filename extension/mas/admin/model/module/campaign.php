<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;

class Campaign extends \Opencart\System\Engine\Model {
    /**
     * Adds a new campaign and its assets.
     *
     * @param array $data The campaign data.
     * @return int The ID of the inserted campaign.
     */
    public function addCampaign(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_campaign` SET `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW(), `date_modified` = NOW()");

        $campaign_id = $this->db->getLastId();

        $this->syncAssets($campaign_id, $data);

        return $campaign_id;
    }

    /**
     * Edits an existing campaign and its assets.
     *
     * @param int $campaign_id The ID of the campaign to edit.
     * @param array $data The new data for the campaign.
     * @return void
     */
    public function editCampaign(int $campaign_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_campaign` SET `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW() WHERE `campaign_id` = '" . (int)$campaign_id . "'");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_campaign_asset` WHERE `campaign_id` = '" . (int)$campaign_id . "'");

        $this->syncAssets($campaign_id, $data);
    }

    /**
     * Deletes a campaign and its asset links.
     *
     * @param int $campaign_id The ID of the campaign to delete.
     * @return void
     */
    public function deleteCampaign(int $campaign_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_campaign` WHERE `campaign_id` = '" . (int)$campaign_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_campaign_asset` WHERE `campaign_id` = '" . (int)$campaign_id . "'");
    }

    /**
     * Retrieves a single campaign and its assets.
     *
     * @param int $campaign_id The ID of the campaign.
     * @return array The campaign data.
     */
    public function getCampaign(int $campaign_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_campaign` WHERE `campaign_id` = '" . (int)$campaign_id . "'");

        if ($query->num_rows) {
            $query->row['assets'] = $this->getCampaignAssets($campaign_id);
            return $query->row;
        } else {
            return [];
        }
    }

    /**
     * Retrieves all campaigns.
     *
     * @param array $data Filter data.
     * @return array A list of campaigns.
     */
    public function getCampaigns(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_campaign` ORDER BY `name` ASC";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Gets the total number of campaigns.
     *
     * @return int
     */
    public function getTotalCampaigns(array $data = []): int {
        $sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_campaign`";

        if (!empty($data['filter_status'])) {
            $sql .= " WHERE `status` = '" . (int)$data['filter_status'] . "'";
        }

        $query = $this->db->query($sql);
        return (int)$query->row['total'];
    }

    /**
     * Finds an active campaign by a specific asset.
     *
     * @param string $asset_type
     * @param int $asset_id
     * @return int campaign_id or 0 if not found
     */
    public function getCampaignByAsset(string $asset_type, int $asset_id): int {
        $query = $this->db->query("SELECT ca.campaign_id FROM `" . DB_PREFIX . "mas_campaign_asset` ca LEFT JOIN `" . DB_PREFIX . "mas_campaign` c ON (ca.campaign_id = c.campaign_id) WHERE ca.asset_type = '" . $this->db->escape($asset_type) . "' AND ca.asset_id = '" . (int)$asset_id . "' AND c.status = 1 LIMIT 1");

        if ($query->num_rows) {
            return (int)$query->row['campaign_id'];
        }

        return 0;
    }

    /**
     * Retrieves all assets for a given campaign.
     *
     * @param int $campaign_id
     * @return array
     */
    public function getCampaignAssets(int $campaign_id): array {
        $assets = [
            'workflows' => [],
            'segments'  => [],
            'templates' => []
        ];
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_campaign_asset` WHERE `campaign_id` = '" . (int)$campaign_id . "'");

        foreach ($query->rows as $row) {
            $assets[$row['asset_type']][] = $row['asset_id'];
        }

        return $assets;
    }

    /**
     * Syncs campaign assets.
     *
     * @param int $campaign_id
     * @param array $data
     * @return void
     */
    private function syncAssets(int $campaign_id, array $data): void {
        if (isset($data['assets']['workflows'])) {
            foreach ($data['assets']['workflows'] as $asset_id) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_campaign_asset` SET `campaign_id` = '" . (int)$campaign_id . "', `asset_type` = 'workflows', `asset_id` = '" . (int)$asset_id . "'");
            }
        }
        if (isset($data['assets']['segments'])) {
            foreach ($data['assets']['segments'] as $asset_id) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_campaign_asset` SET `campaign_id` = '" . (int)$campaign_id . "', `asset_type` = 'segments', `asset_id` = '" . (int)$asset_id . "'");
            }
        }
        if (isset($data['assets']['templates'])) {
            foreach ($data['assets']['templates'] as $asset_id) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_campaign_asset` SET `campaign_id` = '" . (int)$campaign_id . "', `asset_type` = 'templates', `asset_id` = '" . (int)$asset_id . "'");
            }
        }
    }
}