// Textos utilizados pelo seletor de idiomas.
const translations = {
  pt: {
    navProblem: "Problema",
    navHow: "Como funciona",
    navFeatures: "Funcionalidades",
    navRoadmap: "Roadmap",
    navSupport: "Apoiar",
    supportProject: "Apoiar o projeto",
    heroBadge: "Em desenvolvimento · MVP",
    heroTitle: "Divida as gorjetas do restaurante sem dor de cabeça.",
    heroDescription:
      "O tipsforme regista as gorjetas em dinheiro e multibanco de cada turno e divide automaticamente pelos colaboradores presentes.",
    viewFeatures: "Ver funcionalidades",
    previewTitle: "Um painel simples para o gestor do turno",
    previewDescription:
      "Selecione os colaboradores presentes, lance o dinheiro e o multibanco e deixe o cálculo connosco.",
    todayShift: "Turno de hoje",
    openStatus: "Aberto",
    cash: "Dinheiro",
    multibanco: "Multibanco",
    restaurantFee: "Taxa 25%",
    netValue: "Valor líquido: €150,00",
    presentEmployees: "Colaboradores presentes",
    totalToSplit: "Total a dividir",
    perEmployee: "Por colaborador",
    closeShift: "Fechar turno",
    problemTag: "O problema",
    problemTitle: "Dividir gorjetas manualmente gera erros e desconfiança.",
    manualTitle: "Cálculos à mão",
    manualDescription:
      "Somar dinheiro e multibanco de cada turno consome tempo e é fácil errar.",
    feesTitle: "Taxas esquecidas",
    feesDescription:
      "A taxa do restaurante pode ser aplicada automaticamente antes da divisão.",
    historyTitle: "Falta de histórico",
    historyDescription:
      "Sem registo, o colaborador não sabe quanto recebeu nem quando.",
    howTag: "Como funciona",
    howTitle: "Três passos e o turno está fechado.",
    stepOneTitle: "Registar o turno",
    stepOneDescription:
      "O gestor cria um novo lançamento e escolhe a data e o turno.",
    stepTwoTitle: "Lançar os valores",
    stepTwoDescription:
      "Informa dinheiro, multibanco e seleciona quem esteve presente.",
    stepThreeTitle: "Dividir automaticamente",
    stepThreeDescription:
      "O sistema desconta a taxa e divide igualmente entre os colaboradores.",
    featuresTag: "Funcionalidades do MVP",
    featuresTitle: "Tudo o que o restaurante precisa para começar.",
    featureOneTitle: "Dinheiro e multibanco",
    featureOneDescription: "Um único lançamento separa os dois métodos.",
    featureTwoTitle: "Taxa configurável",
    featureTwoDescription: "Percentagem ajustável para cada restaurante.",
    featureThreeTitle: "Divisão automática",
    featureThreeDescription: "Valores repartidos igualmente entre os presentes.",
    featureFourTitle: "Fechos programados",
    featureFourDescription:
      "Dinheiro no dia 15 e no último dia. Multibanco no fecho mensal.",
    featureFiveTitle: "Saldo do colaborador",
    featureFiveDescription: "Cada colaborador acompanha o saldo atualizado.",
    featureSixTitle: "Extrato e histórico",
    featureSixDescription:
      "Consulta simples dos lançamentos e pagamentos realizados.",
    roadmapTitle: "Onde estamos e para onde vamos.",
    phaseOne: "Fase 1",
    phaseTwo: "Fase 2",
    phaseThree: "Fase 3",
    phaseFour: "Fase 4",
    inProgress: "Em curso",
    nextStatus: "Próxima",
    plannedStatus: "Planeado",
    ideasStatus: "Ideias",
    roadmapOneTitle: "MVP funcional",
    roadmapOneDescription:
      "Lançamentos, divisão automática e área do colaborador.",
    roadmapTwoTitle: "Testes com restaurantes reais",
    roadmapTwoDescription: "Projeto-piloto e melhorias de experiência.",
    roadmapThreeTitle: "Relatórios e exportações",
    roadmapThreeDescription: "Exportação de fechos em PDF e CSV.",
    roadmapFourTitle: "Multi-restaurante e app móvel",
    roadmapFourDescription:
      "Gestão de várias casas e aplicação para colaboradores.",
    portfolioTag: "Projeto de portfólio",
    portfolioTitle: "Construído em público, do problema até à publicação.",
    portfolioDescription:
      "O tipsforme também demonstra aprendizagem prática em desenvolvimento web, regras de negócio, banco de dados, testes e entrega contínua.",
    supportTag: "Apoiar o projeto",
    supportTitle: "Ajude a manter o tipsforme vivo.",
    supportDescription:
      "As contribuições ajudam a pagar domínio, hospedagem, testes e desenvolvimento. São voluntárias e não representam investimento ou participação financeira.",
    bankTransfer: "Transferência bancária",
    accountHolder: "Titular",
    copyIban: "Copiar IBAN",
    ibanCopied: "IBAN copiado.",
    paypalDescription: "Apoio internacional através do PayPal.",
    supportPaypal: "Apoiar via PayPal",
    stripeDescription:
      "Apoio por cartão através de um Payment Link seguro.",
    supportStripe: "Apoiar via Stripe",
    legalNote:
      "As contribuições são doações voluntárias. Não representam investimento, participação societária ou promessa de retorno financeiro.",
    developmentStatus: "Em desenvolvimento ativo",
  },

  en: {
    navProblem: "Problem",
    navHow: "How it works",
    navFeatures: "Features",
    navRoadmap: "Roadmap",
    navSupport: "Support",
    supportProject: "Support the project",
    heroBadge: "In development · MVP",
    heroTitle: "Split restaurant tips without the headache.",
    heroDescription:
      "tipsforme records cash and card tips from each shift and automatically splits them between the employees who were present.",
    viewFeatures: "View features",
    previewTitle: "A simple dashboard for the shift manager",
    previewDescription:
      "Select the employees, enter cash and card tips, and let the system handle the calculation.",
    todayShift: "Today's shift",
    openStatus: "Open",
    cash: "Cash",
    multibanco: "Card",
    restaurantFee: "25% fee",
    netValue: "Net amount: €150.00",
    presentEmployees: "Employees present",
    totalToSplit: "Total to split",
    perEmployee: "Per employee",
    closeShift: "Close shift",
    problemTag: "The problem",
    problemTitle: "Splitting tips manually creates errors and mistrust.",
    manualTitle: "Manual calculations",
    manualDescription:
      "Adding cash and card tips from every shift takes time and is easy to get wrong.",
    feesTitle: "Forgotten fees",
    feesDescription:
      "The restaurant fee can be applied automatically before the split.",
    historyTitle: "No payment history",
    historyDescription:
      "Without records, employees do not know how much they received or when.",
    howTag: "How it works",
    howTitle: "Three steps and the shift is closed.",
    stepOneTitle: "Create the shift",
    stepOneDescription:
      "The manager creates a new entry and selects the date and shift.",
    stepTwoTitle: "Enter the amounts",
    stepTwoDescription:
      "Enter cash, card tips and select the employees who were present.",
    stepThreeTitle: "Split automatically",
    stepThreeDescription:
      "The system applies the fee and splits the amount equally.",
    featuresTag: "MVP features",
    featuresTitle: "Everything a restaurant needs to get started.",
    featureOneTitle: "Cash and card tips",
    featureOneDescription: "One entry keeps both payment methods separate.",
    featureTwoTitle: "Configurable fee",
    featureTwoDescription: "An adjustable percentage for each restaurant.",
    featureThreeTitle: "Automatic split",
    featureThreeDescription: "Amounts are split equally among present employees.",
    featureFourTitle: "Scheduled payouts",
    featureFourDescription:
      "Cash on the 15th and last day. Card tips at month-end.",
    featureFiveTitle: "Employee balance",
    featureFiveDescription: "Each employee can follow their updated balance.",
    featureSixTitle: "Statement and history",
    featureSixDescription: "A simple view of entries and completed payments.",
    roadmapTitle: "Where we are and where we are going.",
    phaseOne: "Phase 1",
    phaseTwo: "Phase 2",
    phaseThree: "Phase 3",
    phaseFour: "Phase 4",
    inProgress: "In progress",
    nextStatus: "Next",
    plannedStatus: "Planned",
    ideasStatus: "Ideas",
    roadmapOneTitle: "Working MVP",
    roadmapOneDescription:
      "Shift entries, automatic splitting and employee dashboard.",
    roadmapTwoTitle: "Testing with real restaurants",
    roadmapTwoDescription: "Pilot project and usability improvements.",
    roadmapThreeTitle: "Reports and exports",
    roadmapThreeDescription: "Export payouts as PDF and CSV.",
    roadmapFourTitle: "Multi-restaurant and mobile app",
    roadmapFourDescription:
      "Manage multiple locations and offer a mobile employee app.",
    portfolioTag: "Portfolio project",
    portfolioTitle: "Built in public, from the problem to deployment.",
    portfolioDescription:
      "tipsforme also demonstrates practical learning in web development, business rules, databases, testing and continuous delivery.",
    supportTag: "Support the project",
    supportTitle: "Help keep tipsforme alive.",
    supportDescription:
      "Contributions help cover the domain, hosting, testing and development. They are voluntary and do not represent an investment or financial participation.",
    bankTransfer: "Bank transfer",
    accountHolder: "Account holder",
    copyIban: "Copy IBAN",
    ibanCopied: "IBAN copied.",
    paypalDescription: "International support through PayPal.",
    supportPaypal: "Support via PayPal",
    stripeDescription: "Card support through a secure Payment Link.",
    supportStripe: "Support via Stripe",
    legalNote:
      "Contributions are voluntary donations. They do not represent an investment, company ownership or a promise of financial return.",
    developmentStatus: "Actively in development",
  },
};

let currentLanguage = localStorage.getItem("tipsforme-language") || "pt";

const languageButton = document.getElementById("languageButton");
const languageLabel = document.getElementById("languageLabel");
const copyIbanButton = document.getElementById("copyIbanButton");
const ibanValue = document.getElementById("ibanValue");
const toast = document.getElementById("toast");
const currentYear = document.getElementById("currentYear");

// Atualiza todos os textos marcados com data-i18n.
function updateLanguage(language) {
  currentLanguage = language;
  localStorage.setItem("tipsforme-language", language);

  document.documentElement.lang = language === "pt" ? "pt-PT" : "en";
  languageLabel.textContent = language === "pt" ? "PT" : "EN";

  document.querySelectorAll("[data-i18n]").forEach((element) => {
    const key = element.dataset.i18n;
    const translatedText = translations[language][key];

    if (translatedText) {
      element.textContent = translatedText;
    }
  });
}

// Mostra uma mensagem curta no canto inferior.
function showToast(message) {
  toast.textContent = message;
  toast.classList.add("visible");

  window.setTimeout(() => {
    toast.classList.remove("visible");
  }, 2200);
}

// Alterna entre português e inglês.
languageButton.addEventListener("click", () => {
  const nextLanguage = currentLanguage === "pt" ? "en" : "pt";
  updateLanguage(nextLanguage);
});

// Copia o IBAN para a área de transferência.
copyIbanButton.addEventListener("click", async () => {
  try {
    await navigator.clipboard.writeText(ibanValue.textContent.trim());
    showToast(translations[currentLanguage].ibanCopied);
  } catch (error) {
    // Fallback para navegadores antigos ou páginas sem HTTPS.
    const temporaryInput = document.createElement("textarea");
    temporaryInput.value = ibanValue.textContent.trim();
    document.body.appendChild(temporaryInput);
    temporaryInput.select();
    document.execCommand("copy");
    temporaryInput.remove();

    showToast(translations[currentLanguage].ibanCopied);
  }
});

currentYear.textContent = new Date().getFullYear();
updateLanguage(currentLanguage);
