fetch("C:/Users/ASGA/Documents/Projet_BTS/sidebar.html")
    .then(response => response.text())
    .then(data => {
        document.getElementById("sidebar-container").innerHTML = data;
    });

