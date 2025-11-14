<?php 
// No hay lógica PHP en esta parte, pero mantenemos la estructura .php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReflectGit - El Arquitecto de tu Perfil</title>
    <link href="./css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    
    <style> [x-cloak] { display: none !important; } </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-between p-4 md:p-8">

    <header class="w-full max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-brand-primary to-brand-accent">
            ReflectGit
        </h1>
    </header>

    <main class="w-full max-w-6xl mx-auto my-12" 
          x-data="{ 
              step: 1, 
              githubUser: '', 
              error: null,
              techLibrary: {},
              selectedTech: [],
              selectedTemplate: 'tecnologico',
              bioSuggestions: [], 
              selectedBio: '',   
              bioLoading: false, 
              finalMarkdown: '',
              copyStatus: 'Copiar Código', 
              
              showTrophies: true, 
              showContribs: true, 
              
              customInput: {
                  'Backend': '', 'Frontend': '', 'Database': '', 
                  'DevOps': '', 'ML/AI': '', 'Server': '', 'Testing': '', 'Tools': ''
              },
              socials: {
                  linkedin: '', github: '', twitter: '', youtube: '', 
                  tiktok: '', discord: '', telegram: '', whatsapp: '', facebook: '', instagram: '', reddit: ''
              },

              init() {
                  fetch('./obtener_tecnologias.php')
                      .then(response => response.json())
                      .then(data => { 
                           this.techLibrary = data; 
                           if(Object.keys(data).length === 0) {
                               this.error = 'ERROR CRÍTICO: La base de datos está vacía. Ejecuta los SQL INSERT del PASO 17.2.';
                           }
                      })
                      .catch(e => {
                           this.error = 'ERROR CRÍTICO: No se pudo cargar la biblioteca (Verifica XAMPP/db_conexion.php y tu conexión).';
                      });
              },

              addCustomTech(category) {
                  const inputString = this.customInput[category];
                  if (inputString.trim() === '') return;
                  
                  const names = inputString.split(',').map(name => name.trim()).filter(name => name !== '');

                  names.forEach(name => {
                      if (!this.selectedTech.includes(name)) {
                          this.selectedTech.push(name);
                      }
                  });
                  
                  this.customInput[category] = '';
              },
              
              removeCustomTech(name) {
                  this.selectedTech = this.selectedTech.filter(item => item !== name);
              },

              isCustom(name) {
                  for (const category in this.techLibrary) {
                      if (this.techLibrary[category].some(tech => tech.nombre === name)) {
                          return false; 
                      }
                  }
                  return true; 
              },

              async generarBios() {
                  if(this.selectedTech.length === 0) {
                      this.error = 'Por favor, selecciona al menos una tecnología.';
                      this.step = 4; // Vuelve al paso de habilidades
                      return;
                  }
                  
                  this.step = 5; // Nuevo paso 5: Biografía
                  this.bioLoading = true; 
                  this.error = null;
                  
                  try {
                      const response = await fetch('./generador_bio.php', {
                          method: 'POST',
                          headers: { 'Content-Type': 'application/json' },
                          body: JSON.stringify({ tecnologias: this.selectedTech })
                      });
                      const data = await response.json();

                      if (data.error) {
                          this.error = data.error;
                      } else {
                          this.bioSuggestions = data.sugerencias;
                          this.selectedBio = data.sugerencias[0]; 
                      }
                  } catch (e) {
                      this.error = 'No se pudo contactar al motor de IA.';
                  } finally {
                      this.bioLoading = false; 
                  }
              },
              
              async copiarCodigo() {
                  await navigator.clipboard.writeText(this.finalMarkdown + '\n<!-- Generado con ReflectGit (https://github.com/JohnManrique900/ReflectGit) -->\n');
                  this.copyStatus = '✅ ¡Copiado!';
                  setTimeout(() => { this.copyStatus = 'Copiar Código'; }, 1500);
              },

              async generarCodigo() {
                  this.step = 3; 
                  this.error = null;

                  if(this.githubUser.trim() === '') {
                       this.error = '¡Necesitas un usuario de GitHub para tus estadísticas!';
                       this.step = 2; 
                       return;
                  }
                  if(this.selectedTech.length === 0) {
                       this.error = '¡Necesitas seleccionar herramientas para generar código!';
                       this.step = 4; 
                       return;
                  }

                  try {
                      const response = await fetch('./generador.php', {
                          method: 'POST',
                          headers: { 'Content-Type': 'application/json' },
                          body: JSON.stringify({
                              tecnologias: this.selectedTech,
                              usuario: this.githubUser,
                              plantilla: this.selectedTemplate,
                              biografia: this.selectedBio,
                              showTrophies: this.showTrophies, 
                              showContribs: this.showContribs,
                              socials: this.socials
                          })
                      });
                      const data = await response.json();

                      if (data.error) {
                          this.error = data.error;
                          this.step = 6; 
                      } else {
                          this.finalMarkdown = data.markdown_final;
                          this.step = 7; 
                      }
                  } catch (e) {
                      this.error = 'Error al generar el código.';
                      this.step = 6; 
                  }
              }
          }">

        <!-- PASO 1 (LANDING PAGE) -->
        <div x-show="step === 1" class="w-full">
            <div class="relative z-10 p-6 md:p-12 text-center mb-16 bg-brand-surface/70 border border-brand-border rounded-xl shadow-2xl overflow-hidden">
                <div class="relative z-20">
                    <h2 class="text-5xl md:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-brand-primary to-brand-accent mb-4 transform hover:scale-[1.01] transition-transform duration-500">
                        Tu perfil de GitHub, Analizado
                    </h2>
                    <p class="text-xl md:text-2xl text-brand-text mb-4 max-w-4xl mx-auto">
                        **ReflectGit** analiza tu código, genera tu stack y crea tu biografía profesional.
                    </p>
                    <button @click="step = 2" class="mt-8 px-10 py-3 text-lg font-bold bg-gradient-to-r from-brand-primary to-brand-accent rounded-xl text-white shadow-xl hover:shadow-brand-primary/50 transition-all transform hover:scale-[1.02] duration-300 animate-pulse">
                        Comenzar (6 Pasos de Configuración)
                    </button>
                </div>
                <div class="absolute inset-0 z-10 opacity-30 pointer-events-none overflow-hidden">
                    <div class="absolute -left-32 -top-32 w-80 h-80 bg-brand-primary/50 rounded-full blur-3xl opacity-50 transform animate-pulse" style="animation-duration: 10s;"></div>
                    <div class="absolute -right-40 -bottom-40 w-96 h-96 bg-brand-accent/50 rounded-full blur-3xl opacity-50 transform animate-pulse" style="animation-duration: 12s; animation-delay: 5s;"></div>
                </div>
            </div>

            <div class="p-6 md:p-10 space-y-12">
                <h3 class="text-3xl font-semibold text-brand-text border-b border-brand-border pb-2 mb-6 text-center">Ventajas Clave de ReflectGit</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-brand-surface p-6 rounded-lg border border-brand-border/70 hover:border-brand-primary transition-all duration-300 transform hover:scale-[1.02] shadow-lg">
                        <span class="text-brand-primary text-3xl mb-3 block">🧠</span>
                        <h4 class="text-xl font-semibold text-brand-text">Análisis Automático (API)</h4>
                        <p class="text-brand-text-dim mt-2 text-sm">Escaneamos tus repositorios...</p>
                    </div>
                    <div class="bg-brand-surface p-6 rounded-lg border border-brand-border/70 hover:border-brand-primary transition-all duration-300 transform hover:scale-[1.02] shadow-lg">
                        <span class="text-brand-primary text-3xl mb-3 block">✍️</span>
                        <h4 class="text-xl font-semibold text-brand-text">Sugerencia de Biografía IA</h4>
                        <p class="text-brand-text-dim mt-2 text-sm">Recibe 3 biografías profesionales únicas...</p>
                    </div>
                    <div class="bg-brand-surface p-6 rounded-lg border border-brand-border/70 hover:border-brand-primary transition-all duration-300 transform hover:scale-[1.02] shadow-lg">
                        <span class="text-brand-primary text-3xl mb-3 block">🎨</span>
                        <h4 class="text-xl font-semibold text-brand-text">Estilos Exclusivos</h4>
                        <p class="text-brand-text-dim mt-2 text-sm">Elige entre **Tecnológico**, **Corporativo** y **Minimalista**...</p>
                    </div>
                    <div class="bg-brand-surface p-6 rounded-lg border border-brand-border/70 hover:border-brand-primary transition-all duration-300 transform hover:scale-[1.02] shadow-lg">
                        <span class="text-brand-primary text-3xl mb-3 block">📊</span>
                        <h4 class="text-xl font-semibold text-brand-text">Integración de Estadísticas</h4>
                        <p class="text-brand-text-dim mt-2 text-sm">Generamos el código para tus tarjetas de GitHub Stats...</p>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-10 border-t border-brand-border/50 space-y-8 mt-12">
                <h3 class="text-3xl font-semibold text-brand-text border-b border-brand-border pb-2 mb-6 text-center">Comunidad y Código Abierto</h3>
                
                <div class="flex flex-col items-center space-y-6">
                    <a href="https://github.com/JohnManrique900/ReflectGit" target="_blank" class="flex flex-col items-center group cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="currentColor" class="text-brand-text group-hover:text-brand-primary transition-colors duration-300 transform group-hover:scale-110">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.082-.743.082-.729.082-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.304.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.046.138 3.006.404 2.292-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.casd 43.372.823 1.102.823 2.222v3.293c0 .319.192.694.8.576c4.765-1.589 8.2-6.096 8.2-11.396 0-6.627-5.373-12-12-12z"/></svg>
                        <span class="text-3xl font-extrabold mt-2 text-brand-primary">OPEN SOURCE (MIT)</span>
                    </a>
                    <p class="text-brand-text-dim text-center max-w-lg">El código fue construido con **PHP, MySQL y Tailwind CSS** y está 100% disponible...</p>
                </div>
                
                <h4 class="text-xl font-semibold text-brand-text pt-6 pb-4">Preguntas Frecuentes (FAQ)</h4>
                
                <div x-data="{ open_faq: null }" class="space-y-3">
                    
                    <div class="bg-brand-surface p-4 rounded-lg border border-brand-border">
                        <button @click="open_faq = (open_faq === 1 ? null : 1)" class="w-full text-left font-semibold text-brand-text flex justify-between items-center transition-colors hover:text-brand-primary">
                            <span>¿El análisis es 100% exacto?</span>
                            <span x-text="open_faq === 1 ? '−' : '+'" class="text-2xl text-brand-primary font-light transition-transform"></span>
                        </button>
                        <div x-show="open_faq === 1" x-collapse.duration.500ms class="pt-3 text-brand-text-dim border-t border-brand-border/50 mt-3">
                            El análisis es tan bueno como tus repositorios públicos. Si no defines el lenguaje principal en tu repo, no lo detectamos. Siempre puedes añadir tus habilidades en el Paso 5.
                        </div>
                    </div>

                    <div class="bg-brand-surface p-4 rounded-lg border border-brand-border">
                        <button @click="open_faq = (open_faq === 2 ? null : 2)" class="w-full text-left font-semibold text-brand-text flex justify-between items-center transition-colors hover:text-brand-primary">
                            <span>¿Cómo puedo contribuir con nuevas insignias?</span>
                            <span x-text="open_faq === 2 ? '−' : '+'" class="text-2xl text-brand-primary font-light transition-transform"></span>
                        </button>
                        <div x-show="open_faq === 2" x-collapse.duration.500ms class="pt-3 text-brand-text-dim border-t border-brand-border/50 mt-3">
                            ¡Es fácil! Haz un 'fork' del repositorio, añade las nuevas insignias a la base de datos local y envíanos un 'Pull Request' con tus cambios.
                        </div>
                    </div>

                    <div class="bg-brand-surface p-4 rounded-lg border border-brand-border">
                        <button @click="open_faq = (open_faq === 3 ? null : 3)" class="w-full text-left font-semibold text-brand-text flex justify-between items-center transition-colors hover:text-brand-primary">
                            <span>¿Necesito saber programar para usar la herramienta?</span>
                            <span x-text="open_faq === 3 ? '−' : '+'" class="text-2xl text-brand-primary font-light transition-transform"></span>
                        </button>
                        <div x-show="open_faq === 3" x-collapse.duration.500ms class="pt-3 text-brand-text-dim border-t border-brand-border/50 mt-3">
                            No. Solo copia y pega el código final. No es necesario escribir una sola línea de código para usar ReflectGit.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="step !== 1" class="w-full">
            <div class="w-full max-w-3xl mx-auto my-12">
                <div class="bg-brand-surface border border-brand-border rounded-lg shadow-2xl overflow-hidden">
                    
                    <!-- PASO 2: USUARIO (Solo Input) -->
                    <div x-show="step === 2" class="p-6 md:p-10 space-y-6" x-cloak>
                        <h2 class="text-2xl font-semibold text-brand-text mb-4">Paso 2: Usuario de GitHub</h2>
                        <div x-show="error" class="mb-4 p-3 bg-red-800/20 border border-red-500/30 rounded-lg text-red-400" x-text="error" x-cloak></div>
                        
                        <div class="mb-6">
                            <label for="github_user" class="block text-sm font-medium text-brand-text-dim">Tu Usuario de GitHub (Para Tarjetas de Stats)</label>
                            <div class="mt-1">
                                <input type="text" id="github_user" name="github_user" x-model="githubUser" placeholder="ej: JohnManrique900" class="w-full px-4 py-3 bg-brand-bg border border-brand-border rounded-lg text-brand-text placeholder-brand-text-dim/50 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary">
                            </div>
                        </div>
                    </div>
                    
                    <!-- PASO 3: REDES SOCIALES -->
                    <div x-show="step === 3" class="p-6 md:p-10 space-y-6" x-cloak>
                        <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 3: Links y Redes Sociales</h2>
                        <p class="text-brand-text-dim mb-6">Copia la URL completa de tu perfil para generar insignias con iconos. (Dejar vacío si no aplica).</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(link, socialName) in socials" :key="socialName">
                                <div class="relative">
                                    <label :for="socialName" class="block text-sm font-medium text-brand-text-dim capitalize" x-text="socialName"></label>
                                    <input type="url" :id="socialName" :x-model="`socials.${socialName}`" placeholder="https://..." class="w-full px-4 py-3 bg-brand-bg border border-brand-border rounded-lg text-brand-text placeholder-brand-text-dim/50 focus:ring-brand-primary">
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <!-- PASO 4: HABILIDADES (La Gran Biblioteca) -->
                    <div x-show="step === 4" class="p-6 md:p-10 space-y-6" x-cloak>
                        <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 4: Biblioteca Global de Herramientas</h2>
                        
                        <template x-if="Object.keys(techLibrary).length === 0">
                            <div class="p-4 mb-4 bg-red-800/20 border-l-4 border-red-500 text-red-400" x-cloak>
                                <p class="font-semibold text-red-300">Error Crítico de Carga:</p>
                                <p class="text-sm">La biblioteca de tecnologías no se cargó. Por favor, revisa tu conexión a la Base de Datos.</p>
                            </div>
                        </template>

                        <div class="space-y-6" x-show="Object.keys(techLibrary).length > 0">
                            <template x-for="(tecnologias, categoria) in techLibrary" :key="categoria">
                                <div>
                                    <div class="bg-brand-bg/50 p-3 border-l-4 border-brand-primary rounded-md shadow-md mb-3">
                                        <h3 class="text-lg font-extrabold text-brand-primary" x-text="categoria"></h3>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <template x-for="tech in tecnologias" :key="tech.nombre">
                                            <label 
                                                :class="selectedTech.includes(tech.nombre) ? 'border-brand-primary ring-2 ring-brand-primary bg-brand-primary/10 shadow-lg' : 'border-brand-border hover:border-brand-border/50 bg-brand-surface/70'" 
                                                class="flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-all duration-200 transform hover:scale-[1.03]">
                                                
                                                <input type="checkbox" :value="tech.nombre" x-model="selectedTech" class="hidden">
                                                
                                                <div class="flex flex-col items-center space-y-1">
                                                    <img :src="tech.url_insignia" alt="" class="h-5">
                                                    <span 
                                                        :class="selectedTech.includes(tech.nombre) ? 'font-semibold text-brand-text' : 'text-brand-text-dim'" 
                                                        class="text-xs text-center" 
                                                        x-text="tech.nombre"></span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                    
                                    <!-- INPUT PERSONALIZADO (FIX: Múltiples items separados por coma y botón de agregar) -->
                                    <div class="mt-4 flex space-x-2">
                                        <!-- BUG FIX: :x-model ahora es x-model="customInput[categoria]" -->
                                        <input type="text" x-model="customInput[categoria]" :placeholder="'¿Falta algo en ' + categoria + '? (ej: Tool A, Tool B)'" class="w-full px-4 py-2 bg-brand-bg border border-brand-border rounded-lg text-brand-text placeholder-brand-text-dim/50 focus:ring-brand-primary">
                                        <button @click.prevent="addCustomTech(categoria)" class="px-3 py-1 bg-brand-primary rounded-lg text-white font-semibold flex-shrink-0 hover:bg-brand-accent transition-colors">
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Visualización de Custom Items Agregados (FIX VISUAL CRÍTICO) -->
                            <div x-show="selectedTech.some(name => isCustom(name))" class="mt-6" x-cloak>
                                <h3 class="text-xl font-extrabold text-brand-text border-b border-brand-border pb-2 mb-3">Herramientas Personalizadas Seleccionadas</h3>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <template x-for="tech in selectedTech.filter(name => isCustom(name))" :key="tech">
                                        <span 
                                            @click="removeCustomTech(tech)"
                                            class="bg-brand-accent/20 text-brand-text text-xs p-2 rounded-lg px-3 cursor-pointer border border-brand-accent hover:bg-red-500/40 transition-colors">
                                            <span x-text="tech"></span> ❌
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PASO 5: BIOGRAFÍA (Antes Paso 6) -->
                    <div x-show="step === 5" class="p-6 md:p-10" x-cloak>
                        <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 5: Sugerencias de Biografía</h2>
                        <p class="text-brand-text-dim mb-6">Basado en tus habilidades, nuestra IA (simulada) sugiere estas opciones:</p>
                        <div x-show="error" class="mb-4 p-3 bg-red-800/20 border border-red-500/30 rounded-lg text-red-400" x-text="error" x-cloak></div>

                        <div x-show="bioLoading" class="flex justify-center items-center h-48">
                            <div class="w-8 h-8 border-4 border-brand-primary border-t-transparent rounded-full animate-spin"></div>
                        </div>

                        <div x-show="!bioLoading" class="space-y-4" x-cloak>
                            <template x-for="(bio, index) in bioSuggestions" :key="index">
                                 <label :class="selectedBio === bio ? 'border-brand-primary ring-2 ring-brand-primary' : 'border-brand-border hover:border-brand-border/50'" class="block p-4 border rounded-lg cursor-pointer transition-all">
                                    <input type="radio" name="bio_option" x-model="selectedBio" :value="bio" class="hidden">
                                    <p class="text-brand-text" x-text="bio"></p>
                                </label>
                            </template>
                            
                            <label :class="!bioSuggestions.includes(selectedBio) ? 'border-brand-primary ring-2 ring-brand-primary' : 'border-brand-border hover:border-brand-border/50'" class="block p-4 border rounded-lg cursor-pointer transition-all">
                                <input type="radio" name="bio_option" x-model="selectedBio" :value="selectedBio" @click="selectedBio = ''" class="hidden">
                                <p class="text-brand-text-dim mb-2">O escribe la tuya:</p>
                                <textarea x-model="selectedBio" @focus="bioSuggestions.includes(selectedBio) ? selectedBio = '' : null" class="w-full p-2 bg-brand-bg border border-brand-border rounded-lg text-brand-text focus:outline-none focus:ring-0" rows="3" placeholder="Desarrollador web con base en México..."></textarea>
                            </label>
                        </div>
                    </div>

                    <!-- PASO 6: ESTILO Y TARJETAS (Antes Paso 7) -->
                    <div x-show="step === 6" class="p-6 md:p-10" x-cloak>
                        <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 6: Elige Estilo y Tarjetas</h2>
                        
                        <div class="bg-brand-bg/50 p-4 rounded-lg mb-8">
                            <h3 class="text-xl font-extrabold text-brand-primary mb-3">Tarjetas de Estadísticas (Stats)</h3>
                            <div class="space-y-3">
                                <!-- Opción Trofeos -->
                                <label class="flex items-center space-x-3 text-brand-text cursor-pointer">
                                    <input type="checkbox" x-model="showTrophies" class="w-4 h-4 text-brand-primary bg-brand-surface border-brand-border rounded focus:ring-brand-primary">
                                    <span>🏆 Mostrar Trofeos de GitHub</span>
                                </label>
                                <!-- Opción Contribuciones -->
                                <label class="flex items-center space-x-3 text-brand-text cursor-pointer">
                                    <input type="checkbox" x-model="showContribs" class="w-4 h-4 text-brand-primary bg-brand-surface border-brand-border rounded focus:ring-brand-primary">
                                    <span>🔝 Mostrar Contribuciones Anuales</span>
                                </label>
                            </div>
                        </div>

                        <h3 class="text-xl font-extrabold text-brand-primary mb-3">Estilo del Stack</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div @click="selectedTemplate = 'tecnologico'" :class="selectedTemplate === 'tecnologico' ? 'border-brand-primary ring-2 ring-brand-primary bg-brand-primary/10' : 'border-brand-border hover:border-brand-border/50'" class="p-4 border rounded-lg cursor-pointer transition-all">
                                <h3 class="text-lg font-semibold text-brand-text">Tecnológico (Insignias)</h3>
                                <p class="text-sm text-brand-text-dim">Muestra todas las insignias y estadísticas.</p>
                            </div>
                            <div @click="selectedTemplate = 'corporativo'" :class="selectedTemplate === 'corporativo' ? 'border-brand-primary ring-2 ring-brand-primary bg-brand-primary/10' : 'border-brand-border hover:border-brand-border/50'" class="p-4 border rounded-lg cursor-pointer transition-all">
                                <h3 class="text-lg font-semibold text-brand-text">Corporativo (Limpieza)</h3>
                                <p class="text-sm text-brand-text-dim">Basado en listas Markdown simples.</p>
                            </div>
                            <div @click="selectedTemplate = 'minimalista'" :class="selectedTemplate === 'minimalista' ? 'border-brand-primary ring-2 ring-brand-primary bg-brand-primary/10' : 'border-brand-border hover:border-brand-border/50'" class="p-4 border rounded-lg cursor-pointer transition-all">
                                <h3 class="text-lg font-semibold text-brand-text">Minimalista (Texto)</h3>
                                <p class="text-sm text-brand-text-dim">Solo texto.</p>
                            </div>
                        </div>
                    </div>

                    <!-- PASO 7: CÓDIGO FINAL (Antes Paso 8) -->
                    <div x-show="step === 7" class="p-6 md:p-10" x-cloak>
                        <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 7: ¡Tu perfil está listo!</h2>
                        <p class="text-brand-text-dim mb-4">Copia este código y pégalo en el `README.md` de tu repositorio de perfil de GitHub.</p>
                        <textarea readonly x-text="finalMarkdown" class="w-full h-96 p-4 bg-brand-bg border border-brand-border rounded-lg text-brand-text font-mono text-sm whitespace-pre overflow-auto focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary"></textarea>
                    </div>


                    <!-- FOOTER DE NAVEGACIÓN (Modificado) -->
                    <footer class="bg-brand-bg/50 border-t border-brand-border px-6 py-4 flex justify-between items-center">
                        
                        <button 
                            x-show="step === 2 || step === 3 || step === 4 || step === 5 || step === 6 || step === 7"
                            @click="step--"
                            class="px-5 py-2 bg-brand-surface border border-brand-border rounded-lg font-semibold text-brand-text-dim hover:text-brand-text hover:border-brand-text/50 transition-all duration-300">
                            Atrás
                        </button>

                        <button 
                            x-show="step === 1"
                            @click="step = 2"
                            class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto">
                            Comenzar
                        </button>

                        <button 
                            x-show="step === 2"
                            @click="step = 3"
                            :disabled="Object.keys(techLibrary).length === 0 || githubUser.trim() === ''"
                            class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto disabled:opacity-50 disabled:cursor-not-allowed">
                            Siguiente (Redes Sociales)
                        </button>

                        <button 
                            x-show="step === 3"
                            @click="step = 4"
                            class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto">
                            Siguiente (Elegir Habilidades)
                        </button>

                        <button 
                            x-show="step === 4"
                            @click="generarBios()"
                            :disabled="selectedTech.length === 0"
                            class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto disabled:opacity-50 disabled:cursor-not-allowed">
                            Siguiente (Generar Bio)
                        </button>

                        <button 
                            x-show="step === 5"
                            @click="step = 6"
                            :disabled="bioLoading || selectedBio.trim() === ''"
                            class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto disabled:opacity-50">
                            Siguiente (Elegir Estilo)
                        </button>

                        <button 
                            x-show="step === 6"
                            @click="generarCodigo()"
                            class="px-5 py-2 bg-green-600 rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto">
                            Generar mi Código
                        </button>

                        <button 
                            x-show="step === 7"
                            @click="copiarCodigo()"
                            :class="copyStatus === '¡Copiado!' ? 'bg-green-600 hover:bg-green-700' : 'bg-brand-primary hover:bg-brand-accent'"
                            class="px-5 py-2 rounded-lg font-semibold text-white shadow-lg ml-auto transition-colors duration-300 flex items-center space-x-2">
                            <span x-text="copyStatus">Copiar Código</span>
                            <svg x-show="copyStatus === 'Copiar Código'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <svg x-show="copyStatus === '¡Copiado!'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </button>
                    </footer>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="w-full max-w-6xl mx-auto text-center py-6 border-t border-brand-border/50">
        <p class="text-sm text-brand-text-dim">
            Una herramienta Open Source de <span class="font-semibold text-brand-text">JohnManrique900</span>
        </p>
    </footer>

</body>
</html>