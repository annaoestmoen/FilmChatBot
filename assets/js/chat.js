async function sendMessage() {
    const input = document.getElementById("userInput");
    const message = input.value;
    input.value = "";

    // Vis brukerens melding
    document.getElementById("chatlog").innerHTML += `<p><b>Du:</b> ${message}</p>`;

    // Send til backend
    const response = await fetch("backend/chatbot.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({message})
    });

    const data = await response.json();

    // Vis svar
    if(data.error){
        document.getElementById("chatlog").innerHTML += `<p><b>Bot:</b> ${data.error}</p>`;
    } else {
        let html = "<p><b>Bot:</b></p>";
        data.forEach(movie=>{
            html += `<div style="margin-bottom:20px;">
                        <img src="${movie.poster}" alt="${movie.title}">
                        <h3>${movie.title} (${movie.year})</h3>
                        <p>${movie.overview}</p>
                     </div>`;
        });
        document.getElementById("chatlog").innerHTML += html;
    }
}
