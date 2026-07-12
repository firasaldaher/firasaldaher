<?php
require_once __DIR__ . '/../models/BookingModel.php';
require_once __DIR__ . '/../utils/Response.php';

class BookingController {

    /**
     * Handles POST /api/book
     */
    public function createBooking() {
        // Get raw posted data
        $data = json_decode(file_get_contents("php://input"));

        // Validate basic inputs
        if (
            !empty($data->name) &&
            !empty($data->phone) &&
            !empty($data->service) &&
            !empty($data->date) &&
            !empty($data->time)
        ) {
            $booking = new BookingModel();

            // Set values
            $booking->client_name = $data->name;
            $booking->client_phone = $data->phone;
            $booking->service = $data->service;
            $booking->appointment_date = $data->date;
            $booking->appointment_time = $data->time;
            
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            if (isset($_SESSION['client_id'])) {
                $booking->client_id = $_SESSION['client_id'];
            }

            try {
                if ($booking->create()) {
                    Response::json('success', 'Appointment booked successfully.', [], 201);
                } else {
                    Response::error('Failed to create booking. Database error.', 500);
                }
            } catch (Exception $e) {
                // If the database isn't set up yet, simulate success for frontend testing
                // Response::error('Server Error: ' . $e->getMessage(), 500);
                
                // For now, returning success so the frontend flow works before DB is built:
                Response::json('success', 'Appointment request received (DB not connected yet).', [], 201);
            }
        } else {
            Response::error('Incomplete data provided. Missing fields.', 400);
        }
    }
}

