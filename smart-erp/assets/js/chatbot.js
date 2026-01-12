const chatbotToggler = document.querySelector(".chatbot-toggler");
const closeBtn = document.querySelector(".close-btn");
const chatbox = document.querySelector(".chatbox");
const chatInput = document.querySelector(".chat-input textarea");
const sendChatBtn = document.querySelector(".chat-input span");

let userMessage = null; // Variable to store user's message

const inputInitHeight = chatInput.scrollHeight;

// Enhanced Knowledge Base with Navigation Actions
const knowledgeBase = {
    "booking": {
        text: "You can book new services easily. Would you like to view our catalog or see your existing requests?",
        options: [
            { text: "Book New Service", action: "navigate", url: "services.php" },
            { text: "See Prices", action: "chat" }
        ]
    },
    "prices": {
        text: "Our prices are transparent. Laundry is around ₹50/kg, and House Cleaning starts at ₹200. You can see the full price list on the booking page.",
        options: [
            { text: "Go to Booking Page", action: "navigate", url: "services.php" },
            { text: "Refund Policy", action: "chat" }
        ]
    },
    "refund": {
        text: "Refunds for rejected or cancelled requests are typically processed within 3-5 business days to your original payment method.",
        options: [
            { text: "My Activity History", action: "navigate", url: "my_requests.php" },
            { text: "Contact Support", action: "navigate", url: "complaints.php" }
        ]
    },
    "support": {
        text: "We are here to help! You can lodge a complaint or view your documents.",
        options: [
            { text: "Lodge Complaint", action: "navigate", url: "complaints.php" },
            { text: "View Documents", action: "navigate", url: "documents.php" }
        ]
    },
    "default": {
        text: "Hello! I am your Smart Assistant. How can I help you today?",
        options: [
            { text: "Book a Service", action: "chat" },
            { text: "Track Request", action: "navigate", url: "my_requests.php" },
            { text: "Help & Support", action: "chat" }
        ]
    }
};

const createChatLi = (message, className) => {
    // Create a chat <li> element with passed message and className
    const chatLi = document.createElement("li");
    chatLi.classList.add("chat", className);
    // Add robot icon for incoming messages
    let chatContent = className === "outgoing" ? `<p></p>` : `<span class="bot-icon"><i class="fas fa-robot"></i></span><p></p>`;
    chatLi.innerHTML = chatContent;
    chatLi.querySelector("p").textContent = message;
    return chatLi;
}

const showOptions = (options) => {
    if (!options || options.length === 0) return;
    const optionsDiv = document.createElement("div");
    optionsDiv.className = "chat-options incoming";
    optionsDiv.style.marginLeft = "50px"; // Align with bot text

    options.forEach(opt => {
        const btn = document.createElement("button");
        btn.className = "chat-option-btn";
        btn.textContent = opt.text;

        if (opt.action === 'navigate') {
            btn.innerHTML += ' <i class="fas fa-external-link-alt" style="font-size:0.8em"></i>';
            btn.onclick = () => {
                window.location.href = opt.url;
            };
        } else {
            btn.onclick = () => handleOptionClick(opt.text);
        }

        optionsDiv.appendChild(btn);
    });
    chatbox.appendChild(optionsDiv);
    chatbox.scrollTo(0, chatbox.scrollHeight);
}

const generateResponse = (incomingChatLi) => {
    const messageElement = incomingChatLi.querySelector("p");

    // Simple keyword matching rules
    const lowerMsg = userMessage.toLowerCase();
    let responseKey = "default";

    if (lowerMsg.includes("book") || lowerMsg.includes("service") || lowerMsg.includes("order")) responseKey = "booking";
    else if (lowerMsg.includes("price") || lowerMsg.includes("cost") || lowerMsg.includes("rate")) responseKey = "prices";
    else if (lowerMsg.includes("refund") || lowerMsg.includes("cancel") || lowerMsg.includes("money")) responseKey = "refund";
    else if (lowerMsg.includes("support") || lowerMsg.includes("help") || lowerMsg.includes("complaint") || lowerMsg.includes("issue")) responseKey = "support";

    const response = knowledgeBase[responseKey];

    // Typewriter effect simulation (instant for now)
    messageElement.textContent = response.text;
    chatbox.scrollTo(0, chatbox.scrollHeight);

    // Show interactive options
    if (response.options) {
        showOptions(response.options);
    }
}

const handleChat = () => {
    userMessage = chatInput.value.trim();
    if (!userMessage) return;

    // Clear the input textarea and set its height to default
    chatInput.value = "";
    chatInput.style.height = `${inputInitHeight}px`;

    // Append the user's message to the chatbox
    chatbox.appendChild(createChatLi(userMessage, "outgoing"));
    chatbox.scrollTo(0, chatbox.scrollHeight);

    setTimeout(() => {
        // Display "Thinking..." message while waiting for the response
        const incomingChatLi = createChatLi("Thinking...", "incoming");
        chatbox.appendChild(incomingChatLi);
        chatbox.scrollTo(0, chatbox.scrollHeight);
        generateResponse(incomingChatLi);
    }, 600);
}

const handleOptionClick = (text) => {
    userMessage = text;
    chatbox.appendChild(createChatLi(userMessage, "outgoing"));
    chatbox.scrollTo(0, chatbox.scrollHeight);
    setTimeout(() => {
        const incomingChatLi = createChatLi("Thinking...", "incoming");
        chatbox.appendChild(incomingChatLi);
        generateResponse(incomingChatLi);
    }, 500);
}

chatInput.addEventListener("input", () => {
    // Adjust the height of the input textarea based on its content
    chatInput.style.height = `${inputInitHeight}px`;
    chatInput.style.height = `${chatInput.scrollHeight}px`;
});

chatInput.addEventListener("keydown", (e) => {
    // If Enter key is pressed without Shift key and the window 
    // width is greater than 800px, handle the chat
    if (e.key === "Enter" && !e.shiftKey && window.innerWidth > 800) {
        e.preventDefault();
        handleChat();
    }
});

sendChatBtn.addEventListener("click", handleChat);
closeBtn.addEventListener("click", () => document.body.classList.remove("show-chatbot"));
chatbotToggler.addEventListener("click", () => document.body.classList.toggle("show-chatbot"));
