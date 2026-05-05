<?php

$conn = new mysqli("localhost", "root", "", "order system");

// Check if connection failed
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

$customer_name = "";
$order_items = [];
$subtotal = 0;
$discount = 0;
$final = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $customer_name = $_POST['customer_name'] ?? "";
    $products = $_POST['product'] ?? [];
    $quantities = $_POST['qty'] ?? [];

    $prices = [
        "laptop" => 2500,
        "mobile" => 1800,
        "head" => 700
    ];

    foreach ($products as $product) {
        $qty = intval($quantities[$product] ?? 0);

        if ($qty > 0) {
            $price = $prices[$product];
            $total = $qty * $price;
            $subtotal += $total;

            $order_items[] = [
                "name" => ucfirst($product),
                "qty" => $qty,
                "price" => $price,
                "total" => $total
            ];
        }
    }

    // Insert Customer
    $stmt = $conn->prepare("INSERT INTO customers (customer_name) VALUES (?)");
    $stmt->bind_param("s", $customer_name);
    $stmt->execute();
    $customer_id = $conn->insert_id;

    $discount = ($subtotal > 5000) ? $subtotal * 0.1 : 0;
    $final = $subtotal - $discount;

    // Insert Order
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, subtotal, discount, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $customer_id, $subtotal, $discount, $final);
    $stmt->execute();
    $order_id = $conn->insert_id;

    foreach ($order_items as $item) {
        // Find product_id safely
        $stmt = $conn->prepare("SELECT product_id FROM products WHERE product_name=?");
        $stmt->bind_param("s", $item['name']);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $product_id = $row['product_id'] ?? 0;

        // Insert Order Items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $order_id, $product_id, $item['qty'], $item['price'], $item['total']);
        $stmt->execute();
    }

    header('Content-Type: application/json');
    echo json_encode([
        "customer" => $customer_name,
        "items" => $order_items,
        "subtotal" => $subtotal,
        "discount" => $discount,
        "final" => $final,
        "time" => date("d M Y, h:i A")
    ]);

    exit; 
}
?>

<script>
function generate() {
    let form = document.getElementById("orderForm");
    
    // Check if at least one checkbox is checked
    let checkedItems = form.querySelectorAll('input[name="product[]"]:checked');
    if (checkedItems.length === 0) {
        alert("Please select at least one product before submitting!");
        return; // Stops the function here
    }

    let formData = new FormData(form);

    fetch("", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }

        // Show the success message now that the order is done
        document.getElementById("successMsg").classList.remove("hidden");

        document.getElementById("sName").innerText = data.customer;
        document.getElementById("date").innerText = data.time;

        let table = "";
        data.items.forEach(item => {
            table += `
            <tr class="border-b">
                <td class="py-2">${item.name}</td>
                <td>Rs. ${item.price}</td>
                <td>${item.qty}</td>
                <td class="text-right">Rs. ${item.total}</td>
            </tr>`;
        });

        document.querySelector("#invoice tbody").innerHTML = table;
        document.getElementById("sub").innerText = "Rs. " + data.subtotal;
        document.getElementById("disc").innerText = "Rs. " + data.discount;
        document.getElementById("final").innerText = "Rs. " + data.final;
    })
    .catch(err => {
        alert("Something went wrong!");
        console.error(err);
    });
}

function resetForm() {
    document.getElementById("orderForm").reset();
    document.getElementById("sName").innerText = "-";
    document.getElementById("date").innerText = "-";
    document.querySelector("#invoice tbody").innerHTML = "";
    document.getElementById("sub").innerText = "Rs. 0";
    document.getElementById("disc").innerText = "Rs. 0";
    document.getElementById("final").innerText = "Rs. 0";
    
    // Hide the success message again
    document.getElementById("successMsg").classList.add("hidden");
}
</script>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Online Order System</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-orange-50 to-slate-100">

<!-- NAVBAR -->
    <div class="bg-gradient-to-r from-orange-700 to-orange-500 text-orange-50 shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
            <div class="font-semibold flex items-center gap-2">
                <i class="fa fa-shopping-cart"></i> Online Order System
            </div>
            <div class="space-x-6 text-sm">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#order">Order</a>
                <a href="#invoice">Invoice</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6">
<!-- HERO -->
        <div class="px-16 py-12 flex justify-between items-center" id="home">
            <div class="max-w-xl">
                <span class="bg-blue-100 text-blue-600 text-xs px-3 py-1 rounded-full">Web Development Practical Task</span>
                <h1 class="text-4xl font-bold mt-4 leading-snug">Professional Product Order & Bill Generator</h1>
                <p class="text-gray-500 mt-3">Create orders, apply discount automatically, and generate the final invoice using PHP form handling in a complete professional webpage.</p>
                <div class="mt-5 space-x-3">
                    <a href="#order" class="bg-orange-500 text-white px-5 py-2 rounded-lg shadow hover:bg-orange-600 inline-block"><i class="fa fa-paper-plane mr-1"></i> Start Order</a>
                    <a href="#features" class="bg-white px-5 py-2 rounded-lg border shadow-sm inline-block"><i class="fa fa-circle-info mr-1"></i> Explore Features</a>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-md w-[420px] grid grid-cols-2 gap-4 hover:shadow-lg transition">
                <div class="bg-orange-50 p-4 rounded-xl text-center">
                    <div class="font-bold text-lg">3</div>
                    <div class="text-xs text-gray-500">Products Available</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-xl text-center">
                    <div class="font-bold text-lg">10%</div>
                    <div class="text-xs text-gray-500">Discount Above Rs. 5000</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-xl text-center">
                    <div class="font-bold text-lg">PHP</div>
                    <div class="text-xs text-gray-500">Server-side Form Handling</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-xl text-center">
                    <div class="font-bold text-lg">UI</div>
                    <div class="text-xs text-gray-500">Modern Responsive Design</div>
                </div>
            </div>
        </div>

<!-- FEATURES -->
        <div class="px-16 text-center" id="features">
            <h2 class="text-xl font-semibold">Project Features</h2>
            <p class="text-gray-500 text-sm mt-2 mb-6">This task combines HTML, CSS, Bootstrap, JavaScript validation, and PHP in one professional webpage.</p>
            <div class="grid grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl shadow text-left hover:shadow-lg transition">
                    <i class="fa fa-desktop text-orange-500 text-xl mb-3"></i>
                    <h3 class="font-semibold">Responsive Layout</h3>
                    <p class="text-sm text-gray-500">Modern layout using Bootstrap grid and custom CSS styling.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow text-left hover:shadow-lg transition">
                    <i class="fa fa-cart-shopping text-orange-500 text-xl mb-3"></i>
                    <h3 class="font-semibold">Product Ordering</h3>
                    <p class="text-sm text-gray-500">Choose products, set quantities, and submit the complete order form.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow text-left hover:shadow-lg transition">
                    <i class="fa fa-percent text-orange-500 text-xl mb-3"></i>
                    <h3 class="font-semibold">Discount Logic</h3>
                    <p class="text-sm text-gray-500">Automatic 10% discount when subtotal becomes greater than Rs. 5000.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow text-left hover:shadow-lg transition">
                    <i class="fa fa-file-invoice text-orange-500 text-xl mb-3"></i>
                    <h3 class="font-semibold">Invoice Output</h3>
                    <p class="text-sm text-gray-500">Final bill is processed and displayed using PHP POST method.</p>
                </div>
            </div>
        </div>

<!-- ORDER SECTION -->
    <div class="px-16 py-10 grid grid-cols-2 gap-6" id="order">
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <h3 class="font-semibold mb-2"><i class="fa text-orange-500 fa-clipboard mr-2"></i>Place Your Order</h3>
            <p class="text-sm text-gray-500 mb-4">Enter customer details, select products, and set quantities.</p>
            <form id="orderForm">
                <input id="name" placeholder="Customer Name" name="customer_name" class="w-full p-2 border rounded mb-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="border rounded-xl p-4">
                        <label><input type="checkbox" id="laptop" name="product[]" value="laptop"> Laptop</label>
                        <span class="float-right bg-orange-500 text-white text-xs px-2 py-1 rounded">Rs. 2500</span>
                        <p class="text-xs text-gray-500 mt-2">Best for coding, office work, and productivity.</p>
                        <input type="number" id="laptopQty" class="w-full border rounded mt-2 p-1" placeholder="Quantity" name="qty[laptop]">
                    </div>
                    <div class="border rounded-xl p-4">
                        <label><input type="checkbox" id="mobile" name="product[]" value="mobile"> Mobile</label>
                        <span class="float-right bg-orange-500 text-white text-xs px-2 py-1 rounded">Rs. 1800</span>
                        <p class="text-xs text-gray-500 mt-2">Useful for communication and mobile tasks.</p>
                        <input type="number" id="mobileQty" class="w-full border rounded mt-2 p-1" placeholder="Quantity" name="qty[mobile]">
                    </div>
                    <div class="border rounded-xl p-4">
                        <label><input type="checkbox" id="head" name="product[]" value="head"> Headphones</label>
                        <span class="float-right bg-orange-500 text-white text-xs px-2 py-1 rounded">Rs. 700</span>
                        <p class="text-xs text-gray-500 mt-2">Clear audio for study, meetings, and entertainment.</p>
                        <input type="number" id="headQty" class="w-full border rounded mt-2 p-1" placeholder="Quantity" name="qty[head]">
                    </div>
                </div>
                <div class="bg-orange-50 border border-orange-300 rounded-xl p-4 text-sm mt-4">
                    <b>💡 Instructions</b>
                    <ul class="list-disc ml-4 mt-2 text-gray-600">
                        <li>Enter customer name first.</li>
                        <li>Select at least one product.</li>
                        <li>Quantity must be greater than 0.</li>
                        <li>Submit order to generate invoice.</li>
                        <li>10% discount is applied above Rs. 5000.</li>
                    </ul>
                </div>
                <div class="mt-4 space-x-3">
                    <button type="button" onclick="generate()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">Submit Order</button>
                    <button type="button" onclick="resetForm()" class="bg-gray-200 px-4 py-2 rounded-lg">Reset</button>
                </div>
            </form>
        </div>

        <!-- RIGHT SUMMARY -->
        <div class="bg-white p-6 rounded-2xl shadow-md max-w-xl mx-auto" id="invoice">
            <h2 class="text-lg font-semibold mb-1 flex items-center gap-2"><i class="fa fa-receipt"></i> Order Summary</h2>
            <p class="text-sm text-gray-500 mb-4">Your final order invoice will appear here after form submission.</p>
            <div class="flex gap-4 mb-4">
                <div class="bg-gray-100 p-3 rounded-lg w-1/2 text-sm">
                    <p class="text-gray-500">Customer</p>
                    <p class="font-semibold" id="sName"><?php echo $customer_name ?: '-'; ?></p>
                </div>
                <div class="bg-gray-100 p-3 rounded-lg w-1/2 text-sm">
                    <p class="text-gray-500">Date & Time</p>
                    <p class="font-semibold" id="date"><?php echo date("d M Y, h:i A"); ?></p>
                </div>
            </div>
            <table class="w-full text-sm mb-4 border-collapse">
                <thead>
                    <tr class="text-left border-b">
                        <th class="pb-2">Product</th>
                        <th class="pb-2">Price</th>
                        <th class="pb-2">Qty</th>
                        <th class="pb-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <div class="bg-gray-100 p-4 rounded-lg text-sm mb-4">
                <p class="flex justify-between mb-1"><span>Subtotal</span> <span id="sub">Rs. 0</span></p>
                <p class="flex justify-between mb-1"><span>Discount</span> <span id="disc">Rs. 0</span></p>
                <p class="flex justify-between font-semibold"><span>Final Total</span> <span id="final">Rs. 0</span></p>
            </div>
            <div id="successMsg" class="hidden bg-green-100 p-3 rounded-lg text-sm"> Thank you for your order. Your bill has been generated successfully. 
            </div>
        </div>
    </div>
    </div>

<!-- FOOTER -->
    <div class="bg-gradient-to-r from-orange-700 to-orange-500 text-orange-50 shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
            <div><b>Online Order System</b><br>A complete Web Development practice task using HTML, CSS, Bootstrap, JavaScript, and PHP.</div>
            <div class="self-center">Designed for Web Development Mid-Term Practice</div>
        </div>
    </div>
</body>
</html>