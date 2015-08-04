<?php namespace CS\Models\Order;

use CS\Models\AbstractRecord;

class OrderGoogleAnalyticsRecord extends AbstractRecord
{
    protected $orderId;
    protected $source;
    protected $name;
    protected $medium;
    protected $content;
    protected $term;
    protected $firstVisit;
    protected $previousVisit;
    protected $currentVisit;
    protected $timesVisited;
    protected $pagesViewed;
    protected $keys = array(
        'id' => 'id',
        'orderId' => 'order_id',
        'source' => 'source',
        'name' => 'name',
        'medium' => 'medium',
        'content' => 'content',
        'term' => 'term',
        'firstVisit' => 'first_visit',
        'previousVisit' => 'previous_visit',
        'currentVisit' => 'current_visit',
        'timesVisited' => 'times_visited',
        'pagesViewed' => 'pages_viewed',
    );

    public function load($id)
    {
        $data = $this->db->query("SELECT * FROM `orders_google_analytics` WHERE `id` = " . (int)$id)->fetch(\PDO::FETCH_ASSOC);

        if (is_null($data)) {
            throw new \Exception('Unable to load order google analytics record');
        }

        return $this->loadFromArray($data);
    }

    public function loadByOrderId($order_id)
    {
        $data = $this->db->query("SELECT * FROM `orders_google_analytics` WHERE `order_id` = " . (int)$order_id)->fetch(\PDO::FETCH_ASSOC);

        if (is_null($data)) {
            throw new \Exception('Unable to load order google analytics record');
        }

        return $this->loadFromArray($data);
    }

    public function save()
    {
        if ($this->isNew()) {
            $this->id = $this->insertRecord();
            return true;
        } else throw new \Exception('Update is not set!');
    }

    protected function insertRecord()
    {
        $query = $this->db->prepare("
            INSERT INTO `orders_google_analytics`
            SET
                `order_id` = {$this->db->quote($this->orderId)},
                `source` = {$this->db->quote($this->source)},
                `name` = {$this->db->quote($this->name)},
                `medium` = {$this->db->quote($this->medium)},
                `content` = {$this->db->quote($this->content)},
                `term` = {$this->db->quote($this->term)},
                `first_visit` = {$this->db->quote($this->firstVisit)},
                `previous_visit` = {$this->db->quote($this->previousVisit)},
                `current_visit` = {$this->db->quote($this->currentVisit)},
                `times_visited` = {$this->db->quote($this->timesVisited)},
                `pages_viewed` = {$this->db->quote($this->pagesViewed)}");

        if ($query->execute()) {
            return $this->db->lastInsertId();
        } else return false;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function setDataFromCookiesArray(array $data)
    {
        if (isset($data["__utmz"])) {
            list($domain_hash, $timestamp, $session_number, $campaign_numer, $campaign_data) = explode('.', $data["__utmz"], 5);
            parse_str(strtr($campaign_data, "|", "&"));

            // You should tag you campaigns manually to have a full view
            // of your adwords campaigns data.
            // The same happens with Urchin, tag manually to have your campaign data parsed properly.
            if (isset($utmgclid)) {
                $this->source = "google";
                $this->name = "";
                $this->medium = "cpc";
                $this->content = "";
                if (isset($utmcmd)) $this->term = $utmcmd;
            } else {
                if (isset($utmcsr)) $this->source = $utmcsr;
                if (isset($utmccn)) $this->name = $utmccn;
                if (isset($utmcmd)) $this->medium = $utmcmd;
                if (isset($utmcct)) $this->content = $utmcct;
            }

            if (isset($utmctr)) $this->term = $utmctr;
        }

        if (isset($data["__utma"]))
            list($domain_hash, $random_id, $this->firstVisit, $this->previousVisit, $this->currentVisit, $this->timesVisited) = explode('.', $data["__utma"]);

        /*
        if (isset($data["__utmb"]))
            list($domain_hash, $this->pages_viewed, $garbage, $time_beginning_current_session) = explode('.', $data["__utmb"]);
        */
        return $this;
    }
}