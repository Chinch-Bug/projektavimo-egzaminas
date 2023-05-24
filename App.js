import * as React from 'react';
import {useEffect, useState} from 'react';
import { ActivityIndicator, SafeAreaView, StyleSheet, Text, TextInput, View, ScrollView, Button } from 'react-native';
import { SelectList } from 'react-native-dropdown-select-list';

import * as ImagePicker from 'expo-image-picker';

//import Geolocation from 'react-native-geolocation-service';
import * as Location from 'expo-location';
import {NavigationContainer} from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { Table, Row, Rows } from 'react-native-table-component';

let lati = "";
let longi = "";

function NewReport({navigation}){
  const [isLoading, setLoading] = useState(true);
  const [animaltypes, setData] = useState([]);
  let [selectedType, setSelectedType] = React.useState("");
  let [selectedAnimal, setSelectedAnimal] = React.useState("");
  const [desc, setdesc] = useState('');
  const [error, setError] = useState('');
  const [confirm, setConfirm] = useState('');
  const [base64data, setImage] = useState('');

  const reporttypes = [
    {key:'1', value:'Found Animal'},
    {key:'2', value:'Missing Animal'},
  ]

  selectedType = "Found Animal";

  const listTypes = async () => {
    try{
    const response = await fetch('https://5.199.161.42/pettypes.php');
  
    const json = await response.json();
    setData(json.types);
    console.log(json.types[0].value);
    selectedAnimal = json.types[0].value;
    } catch (error) {
    console.error(error);
  } finally {
    setLoading(false);
  }
  }

  useEffect(() => {
    listTypes();
  }, []);

  let uri = "";
  let type;
  let filename = new Date();
  filename += ".png";
  //let base64data = "";

  const pickImageAsync = async () => {
    let result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.All,
      allowsEditing: true,
      base64: true,
    });

    if (!result.canceled) {
      //console.log(result.assets[0]);
    }

    //uri = result.assets[0].uri;
    setImage(result.assets[0].base64);
    CheckImage();
    
    //if(result.assets[0].type){
    //  type = result.assets[0].type;
    //}
    //else{
    //  type = "image";
    //}
  };

  const CheckImage = () =>{
    if (base64data != ""){
      setConfirm("Image selected");
    }
    else{
      setConfirm("No image selected");
    }
  }

  useEffect(() =>{
    CheckImage();
  }, []);

  const FormEval = () =>{
    //add in check for if any fields are blank, and run postdata if it's all good
    let err = "";
    //console.log(desc+uri+lati+longi)
    if((desc !="" && base64data !="") && lati != "" && longi != ""){
      postdata();
    }
    else if(lati != "" && longi != ""){
      err = "Please provide description and image";
    }
    else{
      err = "Error fetching location";
    }

    //console.log(err);
    setError(err);
    CheckImage();
  }

  async function postdata(){
    if(selectedAnimal == 1){
      selectedAnimal = animaltypes[0]['value'];
    }

    const formdata = new FormData();
    formdata.append('image', base64data);
    formdata.append('filename', filename);
    formdata.append('reportType', selectedType);
    formdata.append('animalType', selectedAnimal);
    formdata.append('description', desc);
    formdata.append('latitude', lati);
    formdata.append('longitude', longi);

    //console.log(formdata);

    let res = await fetch("https://5.199.161.42/beaverdemo/fileupload2.php", {
      method:'post',
      body:formdata,
      //headers:{
      //  'Content-Type': 'multipart/form-data',
      //},
    })

    let responseJSON = await res;
    console.log(responseJSON, "responseJSON");
    setError("Report successful!");
    CheckImage();
  }

  return (
    <SafeAreaView style={styles.container}>
    <ScrollView style={styles.scrollView}>
    <Button style={styles.buttons}
      title="View Reports"
      onPress={() =>
        navigation.navigate('List of Reports')
      }
    />
    <Text style={styles.headertext}>Report Type</Text> 
      <SelectList 
      setSelected={(val) => setSelectedType(val)} 
      data={reporttypes}
      defaultOption={reporttypes['0']} 
      save="value"
  />
    <Text style={styles.headertext}>Select Animal Type</Text>  
  {isLoading ? (
      <ActivityIndicator />
    ) : (
      <SelectList 
      setSelected={(val) => setSelectedAnimal(val)} 
      data={animaltypes} 
      defaultOption={animaltypes['0']} 
      save="value"
  />
    )}
  
  <Text style={styles.headertext}>Animal Description</Text>
  <TextInput style={styles.textbox}
    multiline={true}
    onChangeText={newText => setdesc(newText)}
  /> 
  <Button style={styles.buttons} title="Select Image" onPress={pickImageAsync} />
  <Button style={styles.buttons} title="Submit Form" onPress={FormEval} />
  <Text>{confirm}</Text>
  <Text>{error}</Text>
  </ScrollView>
  </SafeAreaView>
  );
};
function ReportList ({navigation}) {
  const [isLoading, setLoading] = useState(true);
  let tableHead = ['Reports'];
  let widthArr = [100, 500];
const [data, setData] = useState();
const TableOne = () => {
    return (
        <View style={styles.container}>
            <Table>
                <Row data={tableHead} style={styles.head} textStyle={styles.headText} />
                <Rows widthArr={widthArr} borderStyle={{ borderWidth: 1}} data={data} style={styles.tablebody} />
            </Table>
        </View>
    )
}

const fetchReports = async () => {
  const formdata = new FormData();

  formdata.append('latitude', lati);
  formdata.append('longitude', longi);
  
  try{
  const response = await fetch('https://5.199.161.42/reportfetch.php', {
    method:'post',
    body:formdata,
    //headers:{
    //  'Content-Type': 'multipart/form-data',
    //},
  });

  const json = await response.json();
  console.log(json);
  setData(json.reports);
  } catch (error) {
  console.error(error);
} finally {
  setLoading(false);
}
}

useEffect(() => {
  fetchReports();
}, []);
  
  return(
  <SafeAreaView>
  <ScrollView>
  {isLoading ? (
      <ActivityIndicator />
    ) : (
      <TableOne/>
    )}
  </ScrollView>
  </SafeAreaView>
  );
};

const Stack = createNativeStackNavigator();

export default function App() {

  const [location, setLocation] = useState(null);
  const [errorMsg, setErrorMsg] = useState(null);

  useEffect(() => {
    (async () => {
      
      let { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        setErrorMsg('Permission to access location was denied');
        return;
      }

      let location = await Location.getCurrentPositionAsync({});
      setLocation(location);
    })();
  }, []);

  let text = 'Waiting..';
  if (errorMsg) {
    text = errorMsg;
  } else if (location) {
    text = JSON.stringify(location);
    lati = location.coords.latitude;
    longi = location.coords.longitude;
  }

  return (
    <NavigationContainer>
      <Stack.Navigator>
        <Stack.Screen name="Submit New Report" component={NewReport} />
        <Stack.Screen name="List of Reports" component={ReportList} />
      </Stack.Navigator>
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
  },
  scrollView: {
    marginVertical: 50,
  },
  headertext: {
    marginVertical:10,
  },
  textbox:{
    borderColor:'gray',
    borderWidth:1,
    height:80,
  },
  buttons:{
    marginVertical:200,
  },
  head: { height: 44, backgroundColor: 'darkgreen' },
  headText: { fontSize: 20, fontWeight: 'bold' , textAlign: 'center', color: 'white' },
    
});
