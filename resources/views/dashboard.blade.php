<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #ffffff;
            padding: 20px;
            width: 400px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        h1, h2 {
            color: #333;
        }

        img {
            border-radius: 50%;
            margin: 10px 0;
        }

        .form-control, input, select, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        .analytics {
            margin-top: 20px;
        }

        .card {
            background: #fff;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            text-align: left;
        }

        .card h3 {
            margin: 0;
            color: #007bff;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome, {{ Auth::user()->name }}</h1>
        <img src="{{ Auth::user()->avatar }}" alt="Avatar" width="80">
        <p>Email: {{ Auth::user()->email }}</p>
        <button onclick="logout()">Logout</button>

        <h2>Select Your Facebook Page:</h2>
        <select id="facebookPage" class="form-control" onchange="fetchPageAnalytics()">
            <option value="" disabled selected>Loading pages...</option>
        </select>

        <label for="since">Since:</label>
        <input type="date" id="since">

        <label for="until">Until:</label>
        <input type="date" id="until">

        <label for="period">Period:</label>
        <select id="period">
            <option value="total_over_range" selected>Total Over Range</option>
            <option value="day">Daily</option>
            <option value="week">Weekly</option>
        </select>

        <button onclick="fetchPageAnalytics()">Fetch Analytics</button>

        <div id="analytics" class="analytics"></div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetchPages();
        });

        function fetchPages() {
            fetch("{{ route('fetch.facebook.pages') }}")
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", data);

                    if (!data.data || !Array.isArray(data.data)) {
                        console.error("Expected an array but received:", data);
                        return;
                    }

                    let selectBox = document.getElementById("facebookPage");
                    selectBox.innerHTML = '<option value="" disabled selected>Select a page</option>';

                    data.data.forEach(page => {
                        let option = document.createElement("option");
                        option.value = page.id;
                        option.textContent = page.name;
                        selectBox.appendChild(option);
                    });
                })
                .catch(error => console.error("Error fetching pages:", error));
        }

        function fetchPageAnalytics() {
            let selectedPage = document.getElementById("facebookPage").value;
            let since = document.getElementById("since").value;
            let until = document.getElementById("until").value;
            let period = document.getElementById("period").value;

            if (!selectedPage) {
                alert("Please select a Facebook page.");
                return;
            }

            let url = `/fetch-facebook-analytics/${selectedPage}?period=${period}`;
            if (since) url += `&since=${since}`;
            if (until) url += `&until=${until}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log("Analytics Response:", data);

                    if (!Array.isArray(data)) {
                        console.error("Unexpected analytics format:", data);
                        alert("Failed to fetch analytics. Please try again.");
                        return;
                    }

                    let analytics = {
                        "Total Followers": data.find(item => item.name === "page_fans")?.values[0]?.value || 0,
                        "Total Engagement": data.find(item => item.name === "page_engaged_users")?.values[0]?.value || 0,
                        "Total Impressions": data.find(item => item.name === "page_impressions")?.values[0]?.value || 0,
                        "Total Reactions": data.find(item => item.name === "page_actions_post_reactions_total")?.values[0]?.value || 0,
                    };

                    let analyticsDiv = document.getElementById("analytics");
                    analyticsDiv.innerHTML = "";

                    for (const [key, value] of Object.entries(analytics)) {
                        let card = document.createElement("div");
                        card.classList.add("card");
                        card.innerHTML = `<h3>${key}</h3><p>${value}</p>`;
                        analyticsDiv.appendChild(card);
                    }
                })
                .catch(error => {
                    console.error("Error fetching analytics:", error);
                    alert("An error occurred while fetching analytics. Please try again.");
                });
        }

        function logout() {
            window.location.href = "{{ route('logout') }}";
        }
    </script>
</body>

</html>
