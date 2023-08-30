document.getElementById("chat-form").addEventListener("submit", function (event) {
  event.preventDefault();
}, true);

window.onload = (() => {
  setInterval(updateChat, 2000);
  setInterval(getUsers, 2000);
  getMessages();
  getUsers();
})();

setInterval(clearMessages, (1000 * 60) * 30); // 30 Min

document.querySelector("#message").addEventListener("input", function (e) {
  if (e.target.value) {
    document.querySelector("#write-message").style.display = "block";
  } else {
    document.querySelector("#write-message").style.display = "none";
  }
});

function updateChat() {
  getMessages();
}

function clearMessages() {

  const xhr = new XMLHttpRequest();

  xhr.open('POST', 'clearMessages', true);
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
  xhr.open('GET', 'getMessages', true);
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
  xhr.open('GET', 'getUsers', true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {

      const response = JSON.parse(xhr.responseText);

      onlineSpan.innerHTML = response.count + " Online";

      let html = "";

      response.list.map((item) => {

        html = html + "<div class='online-user'><p>" + item.user + "</p> <button class='action-button'><i class='fa fa-comment'></i></button></div>";
      })

      onlineList.innerHTML = html;
    }
  };
  xhr.send();
}

function sendMessage() {

  document.querySelector("#write-message").style.display = "none";

  const messageInput = document.getElementById('message');
  const message = messageInput.value.trim();

  if (message !== '') {
    const xhr = new XMLHttpRequest();

    xhr.open('POST', 'sendMessage', true);
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

function copyCode(button) {
  const message = button.parentElement.parentElement.parentElement.querySelector('p');
  const textArea = document.createElement('textarea');
  textArea.value = message.textContent;
  document.body.appendChild(textArea);
  textArea.select();
  document.execCommand('copy');
  document.body.removeChild(textArea);
  button.textContent = 'Copiado!';
  setTimeout(() => {
    button.textContent = 'Copiar';
  }, 2000);
}

function responseMessage(button) {
  const message = button.parentElement.parentElement.parentElement.querySelector('p');
  const input = document.getElementById('message');
  input.value = message.textContent + ': ';
}

function logout() {
  const xhr = new XMLHttpRequest();

  xhr.open('POST', 'logout', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      window.location.reload();
    }
  };
  xhr.send();
}