document.getElementById("chat-form").addEventListener("submit", function (event) {
    event.preventDefault();
}, true)

window.onload = (() => {
    setInterval(updateChat, 2000);
    setInterval(getUsers,2000);
    getMessages();
    getUsers();
})();

setInterval(clearMessages, (1000 * 60) * 30); // 30 Min

function updateChat() {
    getMessages();
}

function clearMessages() {

    const xhr = new XMLHttpRequest();

    xhr.open('POST', 'logic/ChatControl.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            getMessages();
        }
    };
    xhr.send('clear=true');
}

function getMessages() {

    const chatWindow = document.getElementById('chat-window');

    const isScrolledToBottom = chatWindow.scrollHeight - chatWindow.clientHeight <= chatWindow.scrollTop + 1;

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'logic/ChatControl.php?getMessages', true);
    xhr.onreadystatechange = function () {

        if (xhr.readyState === 4 && xhr.status === 200) {

            chatWindow.innerHTML = xhr.responseText;

            if (isScrolledToBottom) {

                chatWindow.scrollTop = chatWindow.scrollHeight;
            } 
        }
    };
    xhr.send();
}

function getUsers() {

    const onlineSpan = document.getElementById('numberOfUsers');
    const onlineList = document.getElementById('users-list');

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'logic/ChatControl.php?getUsers', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {

            const response = JSON.parse(xhr.responseText);

            onlineSpan.innerHTML = response.count+" Online";

            let html = "";

            response.list.map((item) => {

                html = html + "<div class='online-user'><p>"+item.user+"</p></div>";
            })
            
            onlineList.innerHTML = html;
        }
    };
    xhr.send();
}

function sendMessage() {

    const messageInput = document.getElementById('message');
    const message      = messageInput.value.trim();

    if (message !== '') {

        const xhr = new XMLHttpRequest();

        xhr.open('POST', 'logic/ChatControl.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                getMessages();
                messageInput.value = '';
            }
        };
        xhr.send('message=' + encodeURIComponent(message) + '&action=send-text');
    }
}

function scrollChat() {
    const chatWindow = document.getElementById('chat-window');
    chatWindow.scrollTop = chatWindow.scrollHeight;
}
