const emojiButtons = document.querySelectorAll('.emoji-btn');
const messageInput = document.getElementById('message');

emojiButtons.forEach(button => {
    button.addEventListener('click', function() {
        const emoji = this.getAttribute('data-emoji');
        messageInput.value += emoji;
    });
});

function openModal()
{
    const emojiDiv = document.getElementById('emojiModal');
    emojiDiv.style.display = emojiDiv.style.display === 'none' || emojiDiv.style.display === '' ? 'block' : 'none';
}