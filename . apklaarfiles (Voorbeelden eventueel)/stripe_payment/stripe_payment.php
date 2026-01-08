<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Stripe key

\Stripe\Stripe::setApiKey($stripe_secret_key);

function handleStripePayment($data, $conn)
{
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'create-checkout-session':
            createCheckoutSession($data, $conn);
            break;

        case 'verify-payment':
            verifyPayment($data, $conn);
            break;

    }
}

function createCheckoutSession($data, $conn)
{
    $invoiceId = $data['invoiceid'];
    $invoicename = $data['invoicename'] ?? 'Factuur';

    // Haal invoice op uit database
    $getinvoiceSQL = $conn->prepare("SELECT * FROM invoice WHERE invoiceid = ?");
    $getinvoiceSQL->bind_param("i", $invoiceId);
    $getinvoiceSQL->execute();
    $result = $getinvoiceSQL->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Factuur niet gevonden']);
        return;
    }

    $invoice = $result->fetch_assoc();

    // Bereken bedrag in centen (Stripe werkt met centen)
    $amountInCents = (int) ($invoice['cost'] * 100);

    // Maak Stripe sessie aan
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => "http://localhost:3000/dashboard/facturen/betaling-gelukt?session_id={CHECKOUT_SESSION_ID}",
        "cancel_url" => "http://localhost:3000/dashboard/facturen/betaling-mislukt?session_id={CHECKOUT_SESSION_ID}",
        "line_items" => [
            [
                "quantity" => 1,
                "price_data" => [
                    "currency" => "eur",
                    "unit_amount" => $amountInCents,
                    "product_data" => [
                        "name" => $invoice['description'],
                        "description" => "Factuur #" . $invoiceId . " - " . $invoicename
                    ]
                ]
            ]
        ],
        "metadata" => [
            "invoice_id" => $invoiceId
        ]
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Gberuiker naar Stripe gestuurd',
        'data' => [
            'checkoutUrl' => $checkout_session->url,
            'sessionId' => $checkout_session->id
        ]
    ]);

}

function verifyPayment($data, $conn)
{
    $sessionId = $data['session_id'];
    $invoiceId = $data['invoiceid'];

    // Haal Stripe session op
    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    error_log("Stripe status: " . $session->payment_status);

    if ($session->payment_status === 'paid') {
        // Gebruik invoice_id uit metadata als fallback

        // Update invoice in database
        $editInvoiceStatusSQL = $conn->prepare("UPDATE invoice SET status = 'betaald', payed_on = NOW() WHERE invoiceid = ?");
        $editInvoiceStatusSQL->bind_param("i", $invoiceId);
        $result = $editInvoiceStatusSQL->execute();

        if ($result) {
            if ($editInvoiceStatusSQL->affected_rows > 0) {
                error_log("Invoice updated successfully");
                echo json_encode([
                    'success' => true,
                    'message' => 'Betaling succesvol verwerkt!'
                ]);

                AddNotification([
                    "userid" => $data['userid'],
                    "preset" => "invoice_paid",
                    "info" => $data['invoiceid']
                ], $conn);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Factuur niet gevonden of factuur is al betaald.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database fout: ' . mysqli_error($conn)
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Betaling is niet voltooid.'
        ]);
    }
}
?>