from iaLib import agent

import numpy as np
import random
from gymnasium.core import Env

class AgentQLearning(agent.Agent):
    """
    Agente que usa el algoritmo Q-Learning para aprender
    una política para resolver FrozenLake-v1.

    Funcionamiento:
        - Tenemos una tabla Q[s,a] con el valor que tiene realizar
          cada acción en cada estado
        - En cada episodio, el agente elige una acción usando
          una política ε-greedy basada en Q   
        - En cada paso se actualiza Q usando la regla de actualización
          de Q-Learning

          Q(S,A) <- Q(S,A) + α[R + γ max_a Q(S',a) - Q(S,A)]
          donde max_a Q(S',a) es el valor máximo de la acción en el siguiente estado S'
        
        - Una vez entrenado el agente, se define una política greedy
          a partir de Q  
    """
    def __init__(self, gamma, alpha, epsilon, n_episodios, seed=0):
        """
        Args:
            - gamma: factor de descuento (valor de recompensas futuras)
            - alpha: tasa de aprendizaje
            - epsilon: probabilidad de explorar (elegir acción aleatoria)
            - n_episodios: número de episodios de entrenamiento
            - seed: semilla para los generadores de números aleatorios
        """
        super().__init__(long_memoria=0)

        self.n_estados = 16
        self.n_acciones = 4

        self.gamma = gamma
        self.alpha = alpha
        self.epsilon = epsilon
        self.n_episodios = n_episodios

        self.Q = np.zeros((self.n_estados, self.n_acciones), dtype=float)  # Q(s,a) valor de cada acción en cada estado
        self.pi = np.zeros(self.n_estados, dtype=int)    # pi(s) mejor acción en cada estado

        np.random.seed(seed)
        random.seed(seed)

    def epsilon_greedy(self, estado):
        """
        Devuelve una acción siguiendo una política ε-greedy basada en Q.
        """
        if random.random() < self.epsilon:
            # Explorar: elegir acción aleatoria
            return random.randrange(self.n_acciones)
        else:
            # Explotar: elegir la mejor acción según Q
            return int(np.argmax(self.Q[estado]))
        
    def train(self, env: Env):
        """
        Entrena el agente en el entorno usando SARSA
        Args:
            env: entorno de Gymnasium
        """

        # Hacer para cada episodio
        for _ in range(self.n_episodios):
            # Estado inicial
            estado, _ = env.reset()

            terminal = False

            # Para cada paso del episodio
            while not terminal:
                # Elegir acción usando política ε-greedy
                accion = self.epsilon_greedy(estado)

                # Ejecutar accción A y observar R, S'
                nuevo_estado, recompensa, fin, truncar, _ = env.step(accion)
                terminal = fin or truncar

                # Si el nuevo estado no es terminal
                if not terminal:

                    # QLearning
                    self.Q[estado, accion] += self.alpha * (recompensa +
                        self.gamma * np.max(self.Q[nuevo_estado]) - self.Q[estado, accion])
                    
                    # Actualizar estado
                    estado = nuevo_estado
                else:
                    # Caso terminal
                    self.Q[estado, accion] += self.alpha * (recompensa - self.Q[estado, accion])
                
        
        # Construir la política greedy final a partir de Q
        for estado in range(self.n_estados):
            mejor_accion = int(np.argmax(self.Q[estado]))
            self.pi[estado] = mejor_accion

        return self.pi

    def actua(self, estado):
        """
        Devuelve la acción según la política aprendida (greedy)
        """
        return self.pi[estado]       