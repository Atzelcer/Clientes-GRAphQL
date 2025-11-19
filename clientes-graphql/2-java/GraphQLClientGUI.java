import javafx.application.Application;
import javafx.application.Platform;
import javafx.geometry.Insets;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.*;
import javafx.stage.Stage;
import org.json.JSONArray;
import org.json.JSONObject;
import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;

public class GraphQLClientGUI extends Application {
    private static final String API_URL = "http://localhost:8000/graphql";
    private String token = null;
    
    private TextField loginEmail, loginPassword;
    private TextField nombres, apellidos, ci, direccion, telefono, email;
    private TableView<Persona> table;
    private Label statusLabel;
    private BorderPane mainPane;
    private VBox loginPane;

    public static void main(String[] args) {
        launch(args);
    }

    @Override
    public void start(Stage primaryStage) {
        primaryStage.setTitle("GraphQL Client - Personas");
        
        loginPane = createLoginPane();
        mainPane = createMainPane();
        
        Scene scene = new Scene(loginPane, 900, 700);
        primaryStage.setScene(scene);
        primaryStage.show();
    }

    private VBox createLoginPane() {
        VBox vbox = new VBox(15);
        vbox.setPadding(new Insets(20));
        vbox.setStyle("-fx-background-color: #f0f0f0; -fx-alignment: center;");

        VBox card = new VBox(15);
        card.setPadding(new Insets(20));
        card.setStyle("-fx-background-color: white; -fx-border-color: #ddd; -fx-border-width: 1;");
        card.setMaxWidth(400);

        Label title = new Label("Login");
        title.setStyle("-fx-font-size: 20px; -fx-font-weight: bold;");

        loginEmail = new TextField("admin@example.com");
        loginEmail.setPromptText("Email");

        loginPassword = new TextField("password");
        loginPassword.setPromptText("Password");

        Button loginBtn = new Button("Iniciar Sesión");
        loginBtn.setStyle("-fx-background-color: #555; -fx-text-fill: white; -fx-padding: 10 20;");
        loginBtn.setOnAction(e -> login());

        card.getChildren().addAll(title, new Label("Email:"), loginEmail, new Label("Password:"), loginPassword, loginBtn);
        vbox.getChildren().add(card);
        
        return vbox;
    }

    private BorderPane createMainPane() {
        BorderPane border = new BorderPane();
        border.setPadding(new Insets(20));
        border.setStyle("-fx-background-color: #f5f5f5;");

        VBox top = new VBox(10);
        Label title = new Label("CRUD Personas - GraphQL");
        title.setStyle("-fx-font-size: 24px; -fx-font-weight: bold;");
        
        statusLabel = new Label();
        statusLabel.setStyle("-fx-text-fill: green; -fx-font-size: 12px;");
        
        Button logoutBtn = new Button("Cerrar Sesión");
        logoutBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white;");
        logoutBtn.setOnAction(e -> logout());
        
        top.getChildren().addAll(title, statusLabel, logoutBtn);
        border.setTop(top);

        VBox center = new VBox(20);
        center.setPadding(new Insets(20));
        
        VBox formCard = createFormCard();
        VBox tableCard = createTableCard();
        
        center.getChildren().addAll(formCard, tableCard);
        border.setCenter(center);

        return border;
    }

    private VBox createFormCard() {
        VBox card = new VBox(10);
        card.setPadding(new Insets(20));
        card.setStyle("-fx-background-color: white; -fx-background-radius: 10;");

        Label title = new Label("Crear Persona");
        title.setStyle("-fx-font-size: 18px; -fx-font-weight: bold; -fx-text-fill: #667eea;");

        GridPane grid = new GridPane();
        grid.setHgap(10);
        grid.setVgap(10);

        nombres = new TextField();
        apellidos = new TextField();
        ci = new TextField();
        direccion = new TextField();
        telefono = new TextField();
        email = new TextField();

        grid.add(new Label("Nombres:"), 0, 0);
        grid.add(nombres, 1, 0);
        grid.add(new Label("Apellidos:"), 0, 1);
        grid.add(apellidos, 1, 1);
        grid.add(new Label("CI:"), 0, 2);
        grid.add(ci, 1, 2);
        grid.add(new Label("Teléfono:"), 0, 3);
        grid.add(telefono, 1, 3);
        grid.add(new Label("Dirección:"), 0, 4);
        grid.add(direccion, 1, 4);
        grid.add(new Label("Email:"), 0, 5);
        grid.add(email, 1, 5);

        Button createBtn = new Button("Crear");
        createBtn.setStyle("-fx-background-color: #27ae60; -fx-text-fill: white; -fx-padding: 10 20;");
        createBtn.setOnAction(e -> crearPersona());

        card.getChildren().addAll(title, grid, createBtn);
        return card;
    }

    private VBox createTableCard() {
        VBox card = new VBox(10);
        card.setPadding(new Insets(20));
        card.setStyle("-fx-background-color: white; -fx-background-radius: 10;");

        Label title = new Label("Lista de Personas");
        title.setStyle("-fx-font-size: 18px; -fx-font-weight: bold; -fx-text-fill: #667eea;");

        Button refreshBtn = new Button("Actualizar");
        refreshBtn.setStyle("-fx-background-color: #667eea; -fx-text-fill: white;");
        refreshBtn.setOnAction(e -> cargarPersonas());

        table = new TableView<>();
        TableColumn<Persona, Integer> idCol = new TableColumn<>("ID");
        idCol.setCellValueFactory(new PropertyValueFactory<>("id"));
        
        TableColumn<Persona, String> nombresCol = new TableColumn<>("Nombres");
        nombresCol.setCellValueFactory(new PropertyValueFactory<>("nombres"));
        
        TableColumn<Persona, String> apellidosCol = new TableColumn<>("Apellidos");
        apellidosCol.setCellValueFactory(new PropertyValueFactory<>("apellidos"));
        
        TableColumn<Persona, String> ciCol = new TableColumn<>("CI");
        ciCol.setCellValueFactory(new PropertyValueFactory<>("ci"));
        
        TableColumn<Persona, String> telefonoCol = new TableColumn<>("Teléfono");
        telefonoCol.setCellValueFactory(new PropertyValueFactory<>("telefono"));
        
        TableColumn<Persona, String> emailCol = new TableColumn<>("Email");
        emailCol.setCellValueFactory(new PropertyValueFactory<>("email"));
        
        TableColumn<Persona, String> direccionCol = new TableColumn<>("Dirección");
        direccionCol.setCellValueFactory(new PropertyValueFactory<>("direccion"));

        TableColumn<Persona, Void> actionsCol = new TableColumn<>("Acciones");
        actionsCol.setCellFactory(param -> new TableCell<>() {
            private final Button deleteBtn = new Button("Eliminar");
            {
                deleteBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white;");
                deleteBtn.setOnAction(event -> {
                    Persona persona = getTableView().getItems().get(getIndex());
                    eliminarPersona(persona.getId());
                });
            }
            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                setGraphic(empty ? null : deleteBtn);
            }
        });

        table.getColumns().addAll(idCol, nombresCol, apellidosCol, ciCol, telefonoCol, emailCol, direccionCol, actionsCol);
        card.getChildren().addAll(title, refreshBtn, table);
        return card;
    }

    private JSONObject graphqlRequest(String query, JSONObject variables) throws Exception {
        URL url = new URL(API_URL);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("POST");
        conn.setRequestProperty("Content-Type", "application/json");
        if (token != null) {
            conn.setRequestProperty("Authorization", "Bearer " + token);
        }
        conn.setDoOutput(true);

        JSONObject requestBody = new JSONObject();
        requestBody.put("query", query);
        if (variables != null) {
            requestBody.put("variables", variables);
        }

        OutputStream os = conn.getOutputStream();
        os.write(requestBody.toString().getBytes());
        os.flush();

        BufferedReader br = new BufferedReader(new InputStreamReader(conn.getInputStream()));
        StringBuilder response = new StringBuilder();
        String line;
        while ((line = br.readLine()) != null) {
            response.append(line);
        }
        br.close();

        return new JSONObject(response.toString());
    }

    private void login() {
        try {
            String query = "query Login($email: String!, $password: String!) { login(email: $email, password: $password) }";
            JSONObject variables = new JSONObject();
            variables.put("email", loginEmail.getText());
            variables.put("password", loginPassword.getText());

            JSONObject response = graphqlRequest(query, variables);
            token = response.getJSONObject("data").getString("login");
            
            Platform.runLater(() -> {
                Stage stage = (Stage) loginPane.getScene().getWindow();
                stage.getScene().setRoot(mainPane);
                statusLabel.setText("Token: " + token.substring(0, 50) + "...");
                cargarPersonas();
            });
        } catch (Exception e) {
            showAlert("Error", e.getMessage());
        }
    }

    private void logout() {
        token = null;
        Platform.runLater(() -> {
            Stage stage = (Stage) mainPane.getScene().getWindow();
            stage.getScene().setRoot(loginPane);
        });
    }

    private void crearPersona() {
        try {
            String mutation = "mutation CreatePersona($nombres: String!, $apellidos: String!, $ci: String!, $direccion: String, $telefono: String, $email: String) { createPersona(nombres: $nombres, apellidos: $apellidos, ci: $ci, direccion: $direccion, telefono: $telefono, email: $email) { id nombres apellidos ci } }";
            
            JSONObject variables = new JSONObject();
            variables.put("nombres", nombres.getText());
            variables.put("apellidos", apellidos.getText());
            variables.put("ci", ci.getText());
            variables.put("direccion", direccion.getText());
            variables.put("telefono", telefono.getText());
            variables.put("email", email.getText());

            graphqlRequest(mutation, variables);
            
            Platform.runLater(() -> {
                nombres.clear();
                apellidos.clear();
                ci.clear();
                direccion.clear();
                telefono.clear();
                email.clear();
                cargarPersonas();
                showAlert("Éxito", "Persona creada");
            });
        } catch (Exception e) {
            showAlert("Error", e.getMessage());
        }
    }

    private void cargarPersonas() {
        try {
            String query = "query { personas { id nombres apellidos ci direccion telefono email } }";
            JSONObject response = graphqlRequest(query, null);
            JSONArray personas = response.getJSONObject("data").getJSONArray("personas");
            
            Platform.runLater(() -> {
                table.getItems().clear();
                for (int i = 0; i < personas.length(); i++) {
                    JSONObject p = personas.getJSONObject(i);
                    table.getItems().add(new Persona(
                        p.getInt("id"),
                        p.getString("nombres"),
                        p.getString("apellidos"),
                        p.getString("ci"),
                        p.optString("direccion", ""),
                        p.optString("telefono", ""),
                        p.optString("email", "")
                    ));
                }
            });
        } catch (Exception e) {
            showAlert("Error", e.getMessage());
        }
    }

    private void eliminarPersona(int id) {
        try {
            String mutation = "mutation DeletePersona($id: Int!) { deletePersona(id: $id) }";
            JSONObject variables = new JSONObject();
            variables.put("id", id);

            graphqlRequest(mutation, variables);
            
            Platform.runLater(() -> {
                cargarPersonas();
                showAlert("Éxito", "Persona eliminada");
            });
        } catch (Exception e) {
            showAlert("Error", e.getMessage());
        }
    }

    private void showAlert(String title, String content) {
        Platform.runLater(() -> {
            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle(title);
            alert.setContentText(content);
            alert.showAndWait();
        });
    }

    public static class Persona {
        private int id;
        private String nombres;
        private String apellidos;
        private String ci;
        private String direccion;
        private String telefono;
        private String email;

        public Persona(int id, String nombres, String apellidos, String ci, String direccion, String telefono, String email) {
            this.id = id;
            this.nombres = nombres;
            this.apellidos = apellidos;
            this.ci = ci;
            this.direccion = direccion;
            this.telefono = telefono;
            this.email = email;
        }

        public int getId() { return id; }
        public String getNombres() { return nombres; }
        public String getApellidos() { return apellidos; }
        public String getCi() { return ci; }
        public String getDireccion() { return direccion; }
        public String getTelefono() { return telefono; }
        public String getEmail() { return email; }
    }
}
