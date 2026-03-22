<?php

class ReviewsManager {
    private $reviewsFile = 'data/reviews.json';
    
    public function __construct() {
        if (!file_exists('data')) {
            mkdir('data', 0755, true);
        }
        
        if (!file_exists($this->reviewsFile)) {
            file_put_contents($this->reviewsFile, json_encode([]));
        }
    }
    
    public function getAllReviews() {
        if (!file_exists($this->reviewsFile)) {
            return [];
        }
        
        $json = file_get_contents($this->reviewsFile);
        $reviews = json_decode($json, true);
        return is_array($reviews) ? $reviews : [];
    }
    
    public function getActiveReviews() {
        $all = $this->getAllReviews();
        $active = [];
        foreach ($all as $review) {
            if ($review['status'] == 1) {
                $active[] = $review;
            }
        }
        return $active;
    }
    
    public function getReviewById($id) {
        $reviews = $this->getAllReviews();
        foreach ($reviews as $review) {
            if ($review['id'] == $id) {
                return $review;
            }
        }
        return null;
    }
    
    public function addReview($name, $company, $text, $rating = 5) {
        $reviews = $this->getAllReviews();
        
        $newReview = [
            'id' => time() . '_' . rand(1000, 9999),
            'name' => htmlspecialchars(trim($name)),
            'company' => htmlspecialchars(trim($company)),
            'text' => htmlspecialchars(trim($text)),
            'rating' => intval($rating),
            'date' => date('Y-m-d H:i:s'),
            'avatar' => $this->generateAvatar($name),
            'status' => 1
        ];
        
        array_unshift($reviews, $newReview);
        file_put_contents($this->reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $newReview;
    }
    
    public function updateReview($id, $name, $company, $text, $rating, $status = 1) {
        $reviews = $this->getAllReviews();
        
        foreach ($reviews as $key => $review) {
            if ($review['id'] == $id) {
                $reviews[$key]['name'] = htmlspecialchars(trim($name));
                $reviews[$key]['company'] = htmlspecialchars(trim($company));
                $reviews[$key]['text'] = htmlspecialchars(trim($text));
                $reviews[$key]['rating'] = intval($rating);
                $reviews[$key]['status'] = intval($status);
                $reviews[$key]['avatar'] = $this->generateAvatar($name);
                break;
            }
        }
        
        file_put_contents($this->reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    
    public function deleteReview($id) {
        $reviews = $this->getAllReviews();
        
        foreach ($reviews as $key => $review) {
            if ($review['id'] == $id) {
                array_splice($reviews, $key, 1);
                break;
            }
        }
        
        file_put_contents($this->reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    
    public function toggleStatus($id) {
        $reviews = $this->getAllReviews();
        
        foreach ($reviews as $key => $review) {
            if ($review['id'] == $id) {
                $reviews[$key]['status'] = $reviews[$key]['status'] ? 0 : 1;
                break;
            }
        }
        
        file_put_contents($this->reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    
    private function generateAvatar($name) {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= mb_substr($word, 0, 1, 'UTF-8');
            }
            if (mb_strlen($initials, 'UTF-8') >= 2) {
                break;
            }
        }
        
        return strtoupper($initials);
    }
}