const buttons = document.querySelectorAll(".island-btn");

buttons.forEach(button => {

    button.addEventListener("click", () => {

        const islandId = button.dataset.id;
        const islandName = button.dataset.name;

        fetch(`index.php?ajax=1&island_id=${islandId}`)
        .then(response => response.json())
        .then(data => {

            document.getElementById("selectedIsland").innerText =
                islandName;

            document.getElementById("fishCount").innerText =
                data.fish;

            document.getElementById("regionCount").innerText =
                data.region;

            document.getElementById("populationCount").innerText =
                Number(data.population).toLocaleString();

        })
        .catch(error => {
            console.error(error);
        });

    });

});

document
.getElementById("allBtn")
.addEventListener("click", () => {

    window.location.href = "index.php";

});

const darkBtn = document.getElementById("darkModeBtn");

darkBtn.addEventListener("click", () => {

    document.body.classList.toggle("dark");

});