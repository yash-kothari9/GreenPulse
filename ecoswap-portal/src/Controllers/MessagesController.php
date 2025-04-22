<?php
namespace App\Controllers;

use App\Core\Database;

class MessagesController {
    private $db;
    public function __construct() {
        $this->db = new Database();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // Seller/buyer inbox: show all buyers who messaged about each of the seller's listings
    public function inbox() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /sdg-market/public/login');
            exit;
        }
        $userId = $_SESSION['user_id'];
        // Get all listings for this user (seller)
        $stmt = $this->db->query("SELECT id, title FROM listings WHERE user_id = ?", [$userId]);
        $myListings = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $conversations = [];
        foreach ($myListings as $listing) {
            // Find all unique buyers who messaged about this listing
            $buyerStmt = $this->db->query(
                "SELECT DISTINCT u.id, u.name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.listing_id = ? AND m.sender_id != ?",
                [$listing['id'], $userId]
            );
            $buyers = $buyerStmt->fetchAll(\PDO::FETCH_ASSOC);
            $conversations[] = [
                'listing' => $listing,
                'buyers' => $buyers
            ];
        }
        require __DIR__ . '/../../views/messages/inbox.php';
    }

    // Show chat for a listing
    public function chat($listingId = null) {
        if ($listingId === null) {
            $parts = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
            $listingId = $parts[count($parts)-2]; // /listings/{id}/chat
        }
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /sdg-market/public/login');
            exit;
        }
        $stmt = $this->db->query("SELECT * FROM listings WHERE id = ?", [$listingId]);
        $listing = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$listing) {
            http_response_code(404);
            require_once __DIR__ . '/../../views/errors/404.php';
            return;
        }
        $sellerId = $listing['user_id'];
        // If seller is viewing, get buyer_id from GET param
        $buyerId = null;
        if ($userId == $sellerId && isset($_GET['buyer_id'])) {
            $buyerId = (int)$_GET['buyer_id'];
        }
        // Fetch messages: if seller, only between seller and buyer; if buyer, only between buyer and seller
        if ($buyerId) {
            $stmt = $this->db->query(
                "SELECT * FROM messages WHERE listing_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) ORDER BY created_at ASC",
                [$listingId, $sellerId, $buyerId, $buyerId, $sellerId]
            );
        } else {
            $stmt = $this->db->query(
                "SELECT * FROM messages WHERE listing_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) ORDER BY created_at ASC",
                [$listingId, $userId, $sellerId, $sellerId, $userId]
            );
        }
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // Mark notifications as read for this user for this listing and chat (only for messages they received)
        $this->db->query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND message_id IN (
                SELECT id FROM messages WHERE listing_id = ? AND receiver_id = ?
            )",
            [$userId, $listingId, $userId]
        );
        require __DIR__ . '/../../views/messages/chat.php';
    }

    // Send message (POST)
    public function send($listingId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo 'Method Not Allowed'; return;
        }
        if ($listingId === null) {
            $parts = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
            $listingId = $parts[count($parts)-3]; // /listings/{id}/chat/send
        }
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /sdg-market/public/login');
            exit;
        }
        $stmt = $this->db->query("SELECT * FROM listings WHERE id = ?", [$listingId]);
        $listing = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$listing) {
            http_response_code(404); echo 'Listing not found'; return;
        }
        $sellerId = $listing['user_id'];
        $buyerId = null;
        if ($userId == $sellerId && isset($_GET['buyer_id'])) {
            $buyerId = (int)$_GET['buyer_id'];
        }
        if ($userId == $sellerId && $buyerId) {
            $receiverId = $buyerId;
        } else {
            $receiverId = $sellerId;
        }
        // Prevent messaging yourself (should not happen in UI)
        if ($receiverId == $userId) {
            http_response_code(400); echo 'Cannot message yourself'; return;
        }
        $message = trim($_POST['message'] ?? '');
        if ($message === '') {
            $redirect = '/sdg-market/public/listings/' . $listingId . '/chat';
            if ($buyerId) $redirect .= '?buyer_id=' . $buyerId;
            header('Location: ' . $redirect);
            exit;
        }
        $this->db->query(
            "INSERT INTO messages (listing_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)",
            [$listingId, $userId, $receiverId, $message]
        );
        // Get last inserted message ID
        $msgId = $this->db->getLastInsertId();
        // Insert notification for receiver
        $this->db->query(
            "INSERT INTO notifications (user_id, message_id, is_read) VALUES (?, ?, 0)",
            [$receiverId, $msgId]
        );
        $redirect = '/sdg-market/public/listings/' . $listingId . '/chat';
        if ($buyerId) $redirect .= '?buyer_id=' . $buyerId;
        header('Location: ' . $redirect);
        exit;
    }
}
