using System;
using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Drawing;

namespace GraphQLClientGUI
{
    public class MainForm : Form
    {
        private static readonly string API_URL = "http://localhost:8000/graphql";
        private static string token = null;
        private static readonly HttpClient client = new HttpClient();

        private Panel loginPanel;
        private Panel mainPanel;
        private TextBox loginEmail, loginPassword;
        private TextBox nombres, apellidos, ci, direccion, telefono, email;
        private DataGridView dataGrid;
        private Label statusLabel;

        public MainForm()
        {
            Text = "GraphQL Client - Personas";
            Size = new Size(1000, 700);
            StartPosition = FormStartPosition.CenterScreen;

            CreateLoginPanel();
            CreateMainPanel();

            Controls.Add(loginPanel);
            loginPanel.Visible = true;
            mainPanel.Visible = false;
        }

        private void CreateLoginPanel()
        {
            loginPanel = new Panel
            {
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(240, 240, 240)
            };

            Panel card = new Panel
            {
                Size = new Size(400, 250),
                Location = new Point(300, 200),
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle
            };

            Label title = new Label
            {
                Text = "Login",
                Font = new Font("Arial", 18, FontStyle.Bold),
                Location = new Point(150, 20),
                AutoSize = true
            };

            Label lblEmail = new Label { Text = "Email:", Location = new Point(30, 80), AutoSize = true };
            loginEmail = new TextBox { Location = new Point(30, 100), Size = new Size(340, 25), Text = "admin@example.com" };

            Label lblPassword = new Label { Text = "Password:", Location = new Point(30, 130), AutoSize = true };
            loginPassword = new TextBox { Location = new Point(30, 150), Size = new Size(340, 25), Text = "password", UseSystemPasswordChar = true };

            Button loginBtn = new Button
            {
                Text = "Iniciar Sesión",
                Location = new Point(130, 190),
                Size = new Size(140, 35),
                BackColor = Color.FromArgb(85, 85, 85),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat
            };
            loginBtn.Click += async (s, e) => await Login();

            card.Controls.AddRange(new Control[] { title, lblEmail, loginEmail, lblPassword, loginPassword, loginBtn });
            loginPanel.Controls.Add(card);
        }

        private void CreateMainPanel()
        {
            mainPanel = new Panel { Dock = DockStyle.Fill, BackColor = Color.FromArgb(245, 245, 245) };

            statusLabel = new Label
            {
                Location = new Point(20, 20),
                Size = new Size(900, 20),
                ForeColor = Color.Green
            };

            Button logoutBtn = new Button
            {
                Text = "Cerrar Sesión",
                Location = new Point(850, 50),
                Size = new Size(120, 30),
                BackColor = Color.FromArgb(231, 76, 60),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat
            };
            logoutBtn.Click += (s, e) => Logout();

            Panel formPanel = new Panel
            {
                Location = new Point(20, 90),
                Size = new Size(950, 200),
                BackColor = Color.White
            };

            Label formTitle = new Label
            {
                Text = "Crear Persona",
                Font = new Font("Arial", 14, FontStyle.Bold),
                Location = new Point(10, 10),
                AutoSize = true,
                ForeColor = Color.FromArgb(102, 126, 234)
            };

            nombres = CreateField("Nombres:", 10, 50, formPanel);
            apellidos = CreateField("Apellidos:", 320, 50, formPanel);
            ci = CreateField("CI:", 630, 50, formPanel);
            telefono = CreateField("Teléfono:", 10, 110, formPanel);
            direccion = CreateField("Dirección:", 320, 110, formPanel);
            email = CreateField("Email:", 630, 110, formPanel);

            Button createBtn = new Button
            {
                Text = "Crear",
                Location = new Point(400, 160),
                Size = new Size(150, 30),
                BackColor = Color.FromArgb(39, 174, 96),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat
            };
            createBtn.Click += async (s, e) => await CrearPersona();

            formPanel.Controls.Add(formTitle);
            formPanel.Controls.Add(createBtn);

            Panel tablePanel = new Panel
            {
                Location = new Point(20, 300),
                Size = new Size(950, 350),
                BackColor = Color.White
            };

            Label tableTitle = new Label
            {
                Text = "Lista de Personas",
                Font = new Font("Arial", 14, FontStyle.Bold),
                Location = new Point(10, 10),
                AutoSize = true,
                ForeColor = Color.FromArgb(102, 126, 234)
            };

            Button refreshBtn = new Button
            {
                Text = "Actualizar",
                Location = new Point(800, 10),
                Size = new Size(120, 30),
                BackColor = Color.FromArgb(102, 126, 234),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat
            };
            refreshBtn.Click += async (s, e) => await CargarPersonas();

            dataGrid = new DataGridView
            {
                Location = new Point(10, 50),
                Size = new Size(930, 290),
                AllowUserToAddRows = false,
                ReadOnly = true,
                AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill
            };

            dataGrid.Columns.Add("id", "ID");
            dataGrid.Columns.Add("nombres", "Nombres");
            dataGrid.Columns.Add("apellidos", "Apellidos");
            dataGrid.Columns.Add("ci", "CI");
            dataGrid.Columns.Add("telefono", "Teléfono");
            dataGrid.Columns.Add("email", "Email");
            dataGrid.Columns.Add("direccion", "Dirección");

            DataGridViewButtonColumn deleteCol = new DataGridViewButtonColumn
            {
                Name = "delete",
                HeaderText = "Acciones",
                Text = "Eliminar",
                UseColumnTextForButtonValue = true
            };
            dataGrid.Columns.Add(deleteCol);
            dataGrid.CellClick += async (s, e) =>
            {
                if (e.ColumnIndex == dataGrid.Columns["delete"].Index && e.RowIndex >= 0)
                {
                    int id = int.Parse(dataGrid.Rows[e.RowIndex].Cells["id"].Value.ToString());
                    await EliminarPersona(id);
                }
            };

            tablePanel.Controls.AddRange(new Control[] { tableTitle, refreshBtn, dataGrid });

            mainPanel.Controls.AddRange(new Control[] { statusLabel, logoutBtn, formPanel, tablePanel });
        }

        private TextBox CreateField(string label, int x, int y, Panel parent)
        {
            Label lbl = new Label { Text = label, Location = new Point(x, y), AutoSize = true };
            TextBox txt = new TextBox { Location = new Point(x, y + 20), Size = new Size(290, 25) };
            parent.Controls.AddRange(new Control[] { lbl, txt });
            return txt;
        }

        private async Task<JsonDocument> GraphQLRequest(string query, object variables = null)
        {
            var request = new { query = query, variables = variables };
            var content = new StringContent(JsonSerializer.Serialize(request), Encoding.UTF8, "application/json");

            if (token != null)
                client.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", token);

            var response = await client.PostAsync(API_URL, content);
            var responseString = await response.Content.ReadAsStringAsync();
            return JsonDocument.Parse(responseString);
        }

        private async Task Login()
        {
            try
            {
                string query = "query Login($email: String!, $password: String!) { login(email: $email, password: $password) }";
                var variables = new { email = loginEmail.Text, password = loginPassword.Text };
                var response = await GraphQLRequest(query, variables);

                token = response.RootElement.GetProperty("data").GetProperty("login").GetString();
                statusLabel.Text = "Token: " + token.Substring(0, 50) + "...";

                loginPanel.Visible = false;
                mainPanel.Visible = true;
                Controls.Add(mainPanel);

                await CargarPersonas();
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message, "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private void Logout()
        {
            token = null;
            mainPanel.Visible = false;
            loginPanel.Visible = true;
        }

        private async Task CrearPersona()
        {
            try
            {
                string mutation = "mutation CreatePersona($nombres: String!, $apellidos: String!, $ci: String!, $direccion: String, $telefono: String, $email: String) { createPersona(nombres: $nombres, apellidos: $apellidos, ci: $ci, direccion: $direccion, telefono: $telefono, email: $email) { id nombres apellidos ci } }";
                var variables = new
                {
                    nombres = nombres.Text,
                    apellidos = apellidos.Text,
                    ci = ci.Text,
                    direccion = direccion.Text,
                    telefono = telefono.Text,
                    email = email.Text
                };

                await GraphQLRequest(mutation, variables);

                nombres.Clear();
                apellidos.Clear();
                ci.Clear();
                direccion.Clear();
                telefono.Clear();
                email.Clear();

                await CargarPersonas();
                MessageBox.Show("Persona creada", "Éxito", MessageBoxButtons.OK, MessageBoxIcon.Information);
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message, "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private async Task CargarPersonas()
        {
            try
            {
                string query = "query { personas { id nombres apellidos ci direccion telefono email } }";
                var response = await GraphQLRequest(query);
                var personas = response.RootElement.GetProperty("data").GetProperty("personas");

                dataGrid.Rows.Clear();
                foreach (var persona in personas.EnumerateArray())
                {
                    dataGrid.Rows.Add(
                        persona.GetProperty("id").GetInt32(),
                        persona.GetProperty("nombres").GetString(),
                        persona.GetProperty("apellidos").GetString(),
                        persona.GetProperty("ci").GetString(),
                        persona.TryGetProperty("telefono", out var tel) ? tel.GetString() : "",
                        persona.TryGetProperty("email", out var em) ? em.GetString() : "",
                        persona.TryGetProperty("direccion", out var dir) ? dir.GetString() : ""
                    );
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message, "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private async Task EliminarPersona(int id)
        {
            try
            {
                if (MessageBox.Show("¿Eliminar persona?", "Confirmar", MessageBoxButtons.YesNo) == DialogResult.Yes)
                {
                    string mutation = "mutation DeletePersona($id: Int!) { deletePersona(id: $id) }";
                    await GraphQLRequest(mutation, new { id });
                    await CargarPersonas();
                    MessageBox.Show("Persona eliminada", "Éxito", MessageBoxButtons.OK, MessageBoxIcon.Information);
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message, "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        [STAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new MainForm());
        }
    }
}
