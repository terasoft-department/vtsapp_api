import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../Dashboard.dart';
import '../JobCards/Index.dart';
import '../Login.dart';
import '../Requsitions/Index.dart';
import '../UserProfile.dart';
import '../check_lists/CheckLists.dart';
import '../check_lists/CheckListsReport.dart';
import '../device_returns/Index.dart';
import '../installations/Index.dart';
import '../stocks/Allstocks.dart';
import 'History.dart';

class Assignments extends StatefulWidget {
  const Assignments({super.key});

  @override
  _AssignmentsState createState() => _AssignmentsState();
}

class _AssignmentsState extends State<Assignments> {
  final storage = FlutterSecureStorage();
  List<dynamic> assignments = [];
  List<dynamic> filteredAssignments = [];
  bool isLoading = true;
  String searchQuery = '';

  @override
  void initState() {
    super.initState();
    _fetchAssignments();
  }

  Future<void> _fetchAssignments() async {
    try {
      final token = await storage.read(key: 'token');

      if (token == null) {
        _showToast('Token not found');
        setState(() {
          isLoading = false;
        });
        return;
      }

      final response = await http.get(
        Uri.parse('http://147.79.101.245:8082/api/assignments'),
        headers: {
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final jsonResponse = json.decode(response.body);
        setState(() {
          assignments = jsonResponse['assignments'] ?? [];
          filteredAssignments = assignments;
          isLoading = false;
        });
      } else {
        _showToast('Error fetching assignments');
        setState(() {
          isLoading = false;
        });
      }
    } catch (error) {
      _showToast('An error occurred');
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _updateAssignmentStatus(int assignmentId, String status) async {
    final token = await storage.read(key: 'token');

    if (token == null) {
      _showToast('Token not found');
      return;
    }

    final response = await http.put(
      Uri.parse('http://147.79.101.245:8082/api/assignments/$assignmentId'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({'status': status}),
    );

    if (response.statusCode == 200) {
      _showToast('Assignment $status successfully');
      _fetchAssignments(); // Refresh the assignments list
    } else {
      _showToast('Error updating assignment status');
    }
  }

  void _showToast(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }



  void _filterAssignments(String query) {
    setState(() {
      searchQuery = query;
      filteredAssignments = assignments.where((assignment) {
        return assignment['plate_number']
                .toString()
                .toLowerCase()
                .contains(query.toLowerCase()) ||
            assignment['customername']
                .toString()
                .toLowerCase()
                .contains(query.toLowerCase());
      }).toList();
    });
  }

  Future<void> _refreshData() async {
    print("Refresh data called");
    setState(() {
      isLoading = true;
    });
    await _fetchAssignments();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Assignments',
          style: TextStyle(
            fontSize: 15.0,
          ),
        ),
        elevation: 2,
        backgroundColor: Colors.blue,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _refreshData,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refreshData,
        child: isLoading
            ? const Center(child: CircularProgressIndicator())
            : Column(
                children: [
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: TextField(
                      decoration: const InputDecoration(
                        hintText: 'Search by Plate Number or Customer Name',
                        border: OutlineInputBorder(),
                      ),
                      onChanged: _filterAssignments,
                    ),
                  ),
                  Expanded(
                    child: filteredAssignments.isEmpty
                        ? const Center(child: Text('No assignments found'))
                        : SingleChildScrollView(
                            scrollDirection: Axis.vertical,
                            child: Column(
                              children: filteredAssignments.map((assignment) {
                                return Card(
                                  color: Colors.white,
                                  margin: const EdgeInsets.all(8.0),
                                  child: Padding(
                                    padding: const EdgeInsets.all(16.0),
                                    child: Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                'PlateNumber: ${assignment['plate_number']}',
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 12,
                                                ),
                                              ),
                                              Text(
                                                'Client: ${assignment['customername']}',
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                ),
                                              ),
                                              Text(
                                                'Customer Phone: ${assignment['customer_phone']}',
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                ),
                                              ),
                                              Text(
                                                'Location: ${assignment['location']}',
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                ),
                                              ),
                                              Text(
                                                assignment['customer_debt'] == 0
                                                    ? 'Paid'
                                                    : 'Debt: ${assignment['customer_debt']}',
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                ),
                                              ),
                                              Text(
                                                'AssignmentType: ${assignment['case_reported']}',
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                ),
                                              ),
                                              Text(
                                                'Status: ${assignment['status'] ?? 'pending'}',
                                                style: const TextStyle(
                                                    color: Colors.green,
                                                    fontWeight:
                                                        FontWeight.bold),
                                              ),
                                              Text(
                                                'Created At: ${assignment['created_at']}',
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                ),
                                              ),
                                              Text(
                                                'AssignedBy: ${assignment['assigned_by']}',
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                ),
                                              ),
                                              Text(
                                                'Days Passed: ${assignment['days_passed']}',
                                                style: TextStyle(
                                                  fontSize: 12,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(
                                            width:
                                                20), // Space between text and buttons
                                        Column(
                                          children: [
                                            ElevatedButton(
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: Colors
                                                    .green, // Background color for cancel
                                                foregroundColor:
                                                    Colors.white, // Text color
                                              ),
                                              onPressed: () =>
                                                  _updateAssignmentStatus(
                                                      assignment[
                                                          'assignment_id'],
                                                      'accepted'),
                                              child: const Text('Accept'),
                                            ),
                                            const SizedBox(
                                                height:
                                                    8), // Space between buttons
                                            ElevatedButton(
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: Colors
                                                    .red, // Background color for cancel
                                                foregroundColor:
                                                    Colors.white, // Text color
                                              ),
                                              onPressed: () =>
                                                  _updateAssignmentStatus(
                                                      assignment[
                                                          'assignment_id'],
                                                      'not-accepted'),
                                              child: const Text('Decline'),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              }).toList(),
                            ),
                          ),
                  ),
                ],
              ),
      ),
    );
  }
}
