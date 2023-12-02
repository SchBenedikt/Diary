document.getElementById('entryForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Formulardaten sammeln
    var formData = new FormData();
    var entryText = document.querySelector('textarea[name="entry"]').value;
    var imageInput = document.querySelector('input[name="image"]');

    formData.append('entry', entryText);
    formData.append('image', imageInput.files[0]); // Das Bild dem FormData hinzufügen

    // Benutzererwähnungen erkennen und in HTML-Links umwandeln
    entryText = entryText.replace(/@(\w+)/g, '<a href="profile.html?username=$1" style="text-decoration: none; color: black; font-weight: bold;">@$1</a>');

    // AJAX-Anfrage senden
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'create.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('message').innerHTML = xhr.responseText;
        }
    };
    xhr.send(formData);
});

// Verfolge, welche Einträge bereits geliked wurden
const likedEntries = new Set();

// JavaScript-Funktion, um Einträge von insert.php abzurufen und anzuzeigen
function fetchDiaryEntries() {
    fetch('insert.php')
        .then(response => response.json())
        .then(data => {
            const newDiaryEntriesDiv = document.getElementById('newDiaryEntries');
            const now = new Date();

            // Löschen Sie zuerst alle vorhandenen Einträge
            newDiaryEntriesDiv.innerHTML = '';

            data.forEach(entry => {
                const entryDate = new Date(entry.created_at);
                const timeDifferenceDays = (now - entryDate) / (1000 * 60 * 60 * 24);

                const entryElement = document.createElement('div');

                // Konvertiere den Markdown-Text in HTML und füge ihn ein
                const htmlContent = marked(entry.entry_text);

                // Überprüfe, ob der Eintrag bereits geliked wurde
                const isLiked = likedEntries.has(entry.id);

                // Verwende verschiedene Klassen für den "Like"-Button, abhängig davon, ob er bereits geliked wurde
                const likeButtonClass = isLiked ? 'liked' : 'not-liked';

                // HTML-Struktur für den Eintrag mit Like-Button und Like-Zahl vor dem Benutzernamen
                const entryHTML = `
    <div id="entry_${entry.id}" class="entry">
        <div onclick="toggleComments(${entry.id})">${htmlContent}</div>
        <button class="${likeButtonClass}" data-entry-id="${entry.id}" onclick="toggleLike(${entry.id}, '${entry.username}')">
        <i class="fas fa-heart" style="color: red;"></i> Like
        </button>
        <span id="likesCount_${entry.id}" class="likes-count">${entry.likes}</span>
        <a href="profile.html?username=${entry.username}" style="text-decoration: none; color: inherit;">
            <p><strong>${entry.username}</strong></p>
        </a>
                        <small>Erstellt am: ${entry.created_at}</small>
                        <div id="comments_${entry.id}" style="display: none;">
                            <!-- Hier wird das Kommentar-Formular und die Kommentare angezeigt -->
                            <h2>Kommentare:</h2>
                            <div class="comments-section">
                                <form action="add_comment.php" method="post">
                                    <input type="hidden" name="entry_id" value="${entry.id}">
                                    <textarea name="comment_text" placeholder="Dein Kommentar" required></textarea>
                                    <br>
                                    <input type="submit" value="Kommentieren">
                                    
                                </form>
                                <br><br>
                                <!-- Hier werden die Kommentare angezeigt -->
                                <div class="comments">
                                    <!-- Die Kommentare werden hier eingefügt -->
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                entryElement.innerHTML = entryHTML;

                if (timeDifferenceDays <= 365) {
                    newDiaryEntriesDiv.appendChild(entryElement);
                }
            });
        })
        .catch(error => console.error(error));
}


// JavaScript-Funktion zum Kommentare anzeigen
function toggleComments(entryId) {
    const commentsSection = document.getElementById(`comments_${entryId}`);

    if (commentsSection) {
        if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
            // Anzeigen des Kommentarbereichs, wenn er nicht sichtbar ist
            commentsSection.style.display = 'block';

            // Fügen Sie hier den Code hinzu, um die vorhandenen Kommentare zu laden
            fetchComments(entryId); // Diese Funktion sollte die vorhandenen Kommentare abrufen und anzeigen
        } else {
            // Ausblenden des Kommentarbereichs, wenn er sichtbar ist
            commentsSection.style.display = 'none';
        }
    }
}

// JavaScript-Funktion zum Abrufen und Anzeigen von Kommentaren für einen Eintrag
// JavaScript-Funktion zum Abrufen und Anzeigen von Kommentaren für einen Eintrag
// JavaScript-Funktion zum Abrufen und Anzeigen von Kommentaren für einen Eintrag
function fetchComments(entryId) {
    fetch(`get_comments.php?entry_id=${entryId}`)
        .then(response => response.json())
        .then(data => {
            const commentsDiv = document.querySelector(`#entry_${entryId} .comments`);

            // Löschen Sie zuerst alle vorhandenen Kommentare
            commentsDiv.innerHTML = '';

            data.forEach(comment => {
                const commentContainer = document.createElement('div');
                commentContainer.classList.add('comment-container');

                const commentElement = document.createElement('div');
                commentElement.innerHTML = `
                    <p><strong>${comment.username}</strong>: ${comment.comment_text}</p>
                    <button class="comment-like-button" data-comment-id="${comment.id}" onclick="toggleCommentLike(${comment.id}, '${comment.username}')">
                        <i class="fas fa-heart" style="color: ${comment.isLiked ? 'red' : 'inherit'};"></i>
                    </button>
                    <span id="commentLikesCount_${comment.id}" class="comment-likes-count">${comment.likes}</span>
                    <br>
                    <small>Erstellt am: ${comment.created_at_formatted}</small>
                `;

                commentContainer.appendChild(commentElement);
                commentsDiv.appendChild(commentContainer);

                // Füge einen Zeilenumbruch zwischen jedem Kommentar hinzu
                commentsDiv.appendChild(document.createElement('br'));
            });
        })
        .catch(error => console.error(error));
}



// JavaScript-Funktion zum Aktualisieren des Like-Status und der Anzahl der Likes für Einträge
function toggleLike(entryId, username) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `update_likes.php?entry_id=${entryId}&username=${username}`, true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Aktualisiere die Anzeige der Likes auf der Seite
            const likesCountElement = document.getElementById(`likesCount_${entryId}`);
            likesCountElement.innerHTML = xhr.responseText;

            // Aktualisiere den Like-Status des Eintrags
            const likeButton = document.querySelector(`#entry_${entryId} button`);
            likeButton.classList.toggle('liked');

            // Füge oder entferne den Eintrag aus dem Set der gelikten Einträge hinzu
            if (likeButton.classList.contains('liked')) {
                likedEntries.add(entryId);
            } else {
                likedEntries.delete(entryId);
            }
        }
    };

    xhr.send();
}

// JavaScript-Funktion zum Aktualisieren des Like-Status und der Anzahl der Likes für Kommentare
function toggleCommentLike(commentId, username) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `update_comment_likes.php?comment_id=${commentId}&username=${username}`, true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Aktualisiere die Anzeige der Kommentar-Likes auf der Seite
            const commentLikesCountElement = document.getElementById(`commentLikesCount_${commentId}`);
            commentLikesCountElement.innerHTML = xhr.responseText;

            // Aktualisiere den Like-Status des Kommentars
            const likeButton = document.querySelector(`#comment_${commentId} button`);
            likeButton.classList.toggle('liked');
        }
    };

    xhr.send();
}

// JavaScript-Funktion, um das aktuelle Datum und die Uhrzeit zu aktualisieren
function updateCurrentDateTime() {
    const currentDateTimeElement = document.getElementById('currentDateTime');
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: false };
    const currentDateTimeString = now.toLocaleDateString(undefined, options);
    currentDateTimeElement.textContent = currentDateTimeString;
}

// Füge diese Funktion hinzu, um die Anzahl der Benutzer abzurufen
function fetchUserCount() {
    fetch('get_user_count.php') // Ersetze 'get_user_count.php' durch den tatsächlichen Dateinamen
        .then(response => response.json())
        .then(data => {
            const userCountElement = document.getElementById('userCount');
            userCountElement.textContent = data.userCount;
        })
        .catch(error => console.error(error));
}

// Rufe die Benutzeranzahl beim Laden der Seite auf
fetchUserCount();

// Rufen Sie die Funktion zuerst auf, um das aktuelle Datum und die Uhrzeit anzuzeigen
updateCurrentDateTime();

// Aktualisieren Sie das Datum und die Uhrzeit alle 1000 Millisekunden (1 Sekunde)
setInterval(updateCurrentDateTime, 1000);

// Rufen Sie die Einträge beim Laden der Seite auf und nach jeder Aktualisierung
fetchDiaryEntries();

// Aktualisieren Sie die Einträge alle 60 Sekunden
setInterval(fetchDiaryEntries, 60000);
